/*
   +----------------------------------------------------------------------+
   | Copyright (c) The PHP Group                                          |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.01 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available through the world-wide-web at the following url:           |
   | https://www.php.net/license/3_01.txt                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Marcus Boerger <helly@php.net>                              |
   | based on ext/db/db.c by:                                             |
   |          Rasmus Lerdorf <rasmus@php.net>                             |
   |          Jim Winstead <jimw@php.net>                                 |
   +----------------------------------------------------------------------+
 */

/* $Id$ */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_globals.h"

#include <stdlib.h>
#include <string.h>
#include <errno.h>
#ifdef HAVE_UNISTD_H
#include <unistd.h>
#endif

#include "flatfile.h"

#define FLATFILE_BLOCK_SIZE 1024

/* Parse the length prefix in `buf` into `num` and grow `buf` to hold it.
 * atoi() narrows a malformed (e.g. negative) length to a huge size_t whose
 * `+ FLATFILE_BLOCK_SIZE` would overflow erealloc(); the macro yields true in
 * that case so the caller stops reading and the read stays within `buf_size`. */
#define FLATFILE_GROW_BUF(num, buf, buf_size) ( \
	(num) = atoi(buf), \
	(num) >= (buf_size) && ( \
		(num) > SIZE_MAX - FLATFILE_BLOCK_SIZE \
		|| ((buf) = erealloc((buf), (buf_size) = (num) + FLATFILE_BLOCK_SIZE), 0) \
	) \
)

static zend_result flatfile_delete_empty_key(flatfile *dba, zend_off_t start, zend_off_t previous, char *buf)
{
	if (!php_stream_truncate_supported(dba->fp) || !php_stream_gets(dba->fp, buf, 15)) {
		return FAILURE;
	}
	size_t value_length = atoi(buf);
	zend_off_t value_pos = php_stream_tell(dba->fp);
	if (value_pos < 0 || php_stream_seek(dba->fp, 0, SEEK_END) != 0) {
		return FAILURE;
	}
	zend_off_t file_end = php_stream_tell(dba->fp);
	size_t file_size = file_end;
	if (file_end < value_pos || (zend_off_t) file_size != file_end
			|| value_length > file_size - value_pos
			|| php_stream_truncate_set_size(dba->fp, file_size) != 0) {
		return FAILURE;
	}
	zend_off_t end = value_pos + value_length;
	zend_off_t read_pos = end;
	zend_off_t write_pos = start;
	while (read_pos < file_end) {
		size_t count = MIN(FLATFILE_BLOCK_SIZE, file_end - read_pos);
		if (php_stream_seek(dba->fp, read_pos, SEEK_SET) != 0
				|| php_stream_read(dba->fp, buf, count) != count
				|| php_stream_seek(dba->fp, write_pos, SEEK_SET) != 0
				|| php_stream_write(dba->fp, buf, count) != count) {
			return FAILURE;
		}
		read_pos += count;
		write_pos += count;
	}
	if (php_stream_flush(dba->fp) != 0
			|| php_stream_truncate_set_size(dba->fp, write_pos) != 0
			|| php_stream_seek(dba->fp, write_pos, SEEK_SET) != 0) {
		return FAILURE;
	}
	if (dba->CurrentFlatFilePos >= (size_t) end) {
		dba->CurrentFlatFilePos -= end - start;
	} else if (dba->CurrentFlatFilePos > (size_t) start) {
		dba->CurrentFlatFilePos = previous;
	}
	return SUCCESS;
}

/*
 * ret = -1 means that database was opened for read-only
 * ret = 0  success
 * ret = 1  key already exists - nothing done
 */

/* {{{ flatfile_store */
int flatfile_store(flatfile *dba, datum key_datum, datum value_datum, int mode) {
	if (mode == FLATFILE_INSERT) {
		if (flatfile_findkey(dba, key_datum)) {
			return 1;
		}
		php_stream_seek(dba->fp, 0L, SEEK_END);
		php_stream_printf(dba->fp, "%zu\n", key_datum.dsize);
		php_stream_flush(dba->fp);
		if (php_stream_write(dba->fp, key_datum.dptr, key_datum.dsize) < key_datum.dsize) {
			return -1;
		}
		php_stream_printf(dba->fp, "%zu\n", value_datum.dsize);
		php_stream_flush(dba->fp);
		if (php_stream_write(dba->fp, value_datum.dptr, value_datum.dsize) < value_datum.dsize) {
			return -1;
		}
	} else { /* FLATFILE_REPLACE */
		bool empty_key_exists = key_datum.dsize == 0 && flatfile_findkey(dba, key_datum);
		if (flatfile_delete(dba, key_datum) == FAILURE && empty_key_exists) {
			return -1;
		}
		php_stream_printf(dba->fp, "%zu\n", key_datum.dsize);
		php_stream_flush(dba->fp);
		if (php_stream_write(dba->fp, key_datum.dptr, key_datum.dsize) < key_datum.dsize) {
			return -1;
		}
		php_stream_printf(dba->fp, "%zu\n", value_datum.dsize);
		if (php_stream_write(dba->fp, value_datum.dptr, value_datum.dsize) < value_datum.dsize) {
			return -1;
		}
	}

	php_stream_flush(dba->fp);
	return 0;
}
/* }}} */

/* {{{ flatfile_fetch */
datum flatfile_fetch(flatfile *dba, datum key_datum) {
	datum value_datum = {NULL, 0};
	char buf[16];

	if (flatfile_findkey(dba, key_datum)) {
		if (php_stream_gets(dba->fp, buf, sizeof(buf))) {
			value_datum.dsize = atoi(buf);
			value_datum.dptr = safe_emalloc(value_datum.dsize, 1, 1);
			value_datum.dsize = php_stream_read(dba->fp, value_datum.dptr, value_datum.dsize);
		} else {
			value_datum.dptr = NULL;
			value_datum.dsize = 0;
		}
	}
	return value_datum;
}
/* }}} */

/* {{{ flatfile_delete */
int flatfile_delete(flatfile *dba, datum key_datum) {
	char *key = key_datum.dptr;
	size_t size = key_datum.dsize;
	size_t buf_size = FLATFILE_BLOCK_SIZE;
	char *buf = emalloc(buf_size);
	size_t num;
	zend_off_t pos;
	zend_off_t previous = 0;

	php_stream_rewind(dba->fp);
	while(!php_stream_eof(dba->fp)) {
		zend_off_t start = php_stream_tell(dba->fp);
		if (start < 0) {
			break;
		}
		/* read in the length of the key name */
		if (!php_stream_gets(dba->fp, buf, 15)) {
			break;
		}
		if (FLATFILE_GROW_BUF(num, buf, buf_size)) {
			break;
		}
		pos = php_stream_tell(dba->fp);
		if (pos < 0) {
			break;
		}

		/* read in the key name */
		num = php_stream_read(dba->fp, buf, num);

		if (size == num && !memcmp(buf, key, size)) {
			if (size == 0) {
				int result = flatfile_delete_empty_key(dba, start, previous, buf);
				efree(buf);
				return result;
			}
			php_stream_seek(dba->fp, pos, SEEK_SET);
			php_stream_putc(dba->fp, 0);
			php_stream_flush(dba->fp);
			php_stream_seek(dba->fp, 0L, SEEK_END);
			efree(buf);
			return SUCCESS;
		}

		previous = php_stream_tell(dba->fp);
		if (previous < 0) {
			break;
		}
		/* read in the length of the value */
		if (!php_stream_gets(dba->fp, buf, 15)) {
			break;
		}
		if (FLATFILE_GROW_BUF(num, buf, buf_size)) {
			break;
		}
		/* read in the value */
		num = php_stream_read(dba->fp, buf, num);
	}
	efree(buf);
	return FAILURE;
}
/* }}} */

/* {{{ flatfile_findkey */
int flatfile_findkey(flatfile *dba, datum key_datum) {
	size_t buf_size = FLATFILE_BLOCK_SIZE;
	char *buf = emalloc(buf_size);
	size_t num;
	int ret=0;
	void *key = key_datum.dptr;
	size_t size = key_datum.dsize;

	php_stream_rewind(dba->fp);
	while (!php_stream_eof(dba->fp)) {
		if (!php_stream_gets(dba->fp, buf, 15)) {
			break;
		}
		if (FLATFILE_GROW_BUF(num, buf, buf_size)) {
			break;
		}
		num = php_stream_read(dba->fp, buf, num);

		if (size == num) {
			if (!memcmp(buf, key, size)) {
				ret = 1;
				break;
			}
		}
		if (!php_stream_gets(dba->fp, buf, 15)) {
			break;
		}
		if (FLATFILE_GROW_BUF(num, buf, buf_size)) {
			break;
		}
		num = php_stream_read(dba->fp, buf, num);
	}
	efree(buf);
	return ret;
}
/* }}} */

/* {{{ flatfile_firstkey */
datum flatfile_firstkey(flatfile *dba) {
	datum res;
	size_t num;
	size_t buf_size = FLATFILE_BLOCK_SIZE;
	char *buf = emalloc(buf_size);

	php_stream_rewind(dba->fp);
	while(!php_stream_eof(dba->fp)) {
		if (!php_stream_gets(dba->fp, buf, 15)) {
			break;
		}
		if (FLATFILE_GROW_BUF(num, buf, buf_size)) {
			break;
		}
		num = php_stream_read(dba->fp, buf, num);

		if (*(buf) != 0) {
			dba->CurrentFlatFilePos = php_stream_tell(dba->fp);
			res.dptr = buf;
			res.dsize = num;
			return res;
		}
		if (!php_stream_gets(dba->fp, buf, 15)) {
			break;
		}
		if (FLATFILE_GROW_BUF(num, buf, buf_size)) {
			break;
		}
		num = php_stream_read(dba->fp, buf, num);
	}
	efree(buf);
	res.dptr = NULL;
	res.dsize = 0;
	return res;
}
/* }}} */

/* {{{ flatfile_nextkey */
datum flatfile_nextkey(flatfile *dba) {
	if (dba->CurrentFlatFilePos == 0) {
		return flatfile_firstkey(dba);
	}
	datum res;
	size_t num;
	size_t buf_size = FLATFILE_BLOCK_SIZE;
	char *buf = emalloc(buf_size);

	php_stream_seek(dba->fp, dba->CurrentFlatFilePos, SEEK_SET);
	while(!php_stream_eof(dba->fp)) {
		if (!php_stream_gets(dba->fp, buf, 15)) {
			break;
		}
		if (FLATFILE_GROW_BUF(num, buf, buf_size)) {
			break;
		}
		num = php_stream_read(dba->fp, buf, num);

		if (!php_stream_gets(dba->fp, buf, 15)) {
			break;
		}
		if (FLATFILE_GROW_BUF(num, buf, buf_size)) {
			break;
		}
		num = php_stream_read(dba->fp, buf, num);

		if (*(buf)!=0) {
			dba->CurrentFlatFilePos = php_stream_tell(dba->fp);
			res.dptr = buf;
			res.dsize = num;
			return res;
		}
	}
	efree(buf);
	res.dptr = NULL;
	res.dsize = 0;
	return res;
}
/* }}} */

/* {{{ flatfile_version */
const char *flatfile_version(void)
{
	return "1.0, $Id$";
}
/* }}} */
