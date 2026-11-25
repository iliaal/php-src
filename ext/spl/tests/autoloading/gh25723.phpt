--TEST--
GH-25723 (spl_autoload_unregister('spl_autoload_call') UAF when autoloader destructor mutates autoloader stack)
--FILE--
<?php

class SelfUnregister {
    public function autoload(string $class): void {}
    public function __destruct() {
        echo "self-unregister count: ", count(spl_autoload_functions()), "\n";
        echo "self-unregister result: ";
        var_dump(spl_autoload_unregister([$this, 'autoload']));
    }
}

$a = new SelfUnregister();
spl_autoload_register([$a, 'autoload']);
unset($a);
spl_autoload_unregister('spl_autoload_call');

error_reporting(E_ALL & ~E_DEPRECATED);

class Target {
    public static int $dtors = 0;
    public function autoload(string $class): void {}
    public function __destruct() {
        self::$dtors++;
    }
}

class SiblingUnregister {
    public Target $target;
    public function autoload(string $class): void {}
    public function __destruct() {
        echo "sibling-unregister result: ";
        var_dump(spl_autoload_unregister([$this->target, 'autoload']));
    }
}

$t = new Target();
$s = new SiblingUnregister();
$s->target = $t;
spl_autoload_register([$s, 'autoload']);
spl_autoload_register([$t, 'autoload']);
unset($s, $t);
spl_autoload_unregister('spl_autoload_call');

class ClosureRecursive {
    public function makeLoader(): Closure {
        return function (string $class): void {};
    }
    public function __destruct() {
        echo "closure-recursive count: ", count(spl_autoload_functions()), "\n";
        echo "closure-recursive: ";
        var_dump(spl_autoload_unregister('spl_autoload_call'));
    }
}

Target::$dtors = 0;
$c = new ClosureRecursive();
spl_autoload_register($c->makeLoader());
spl_autoload_register([new Target(), 'autoload']);
unset($c);
spl_autoload_unregister('spl_autoload_call');
echo "closure-recursive target dtors: ", Target::$dtors, "\n";

class TrampolineLoader {
    public function __call(string $name, array $args): void {}
    public function __destruct() {
        echo "trampoline-unregister: ";
        var_dump(spl_autoload_unregister([$this, 'missingMethod']));
    }
}

$tr = new TrampolineLoader();
spl_autoload_register([$tr, 'missingMethod']);
unset($tr);
spl_autoload_unregister('spl_autoload_call');

class NamedLoader {
    public function __construct(public string $label) {}
    public function autoload(string $class): void {
        if ($this->label === 'prepended') {
            echo "loaded by {$this->label}: $class\n";
        }
    }
}

class ReregisterOnDestruct {
    public function autoload(string $class): void {}
    public function __destruct() {
        for ($i = 0; $i < 10; $i++) {
            spl_autoload_register([new NamedLoader("append-$i"), 'autoload']);
        }
        spl_autoload_register([new NamedLoader("prepended"), 'autoload'], true, true);
    }
}

$r = new ReregisterOnDestruct();
spl_autoload_register([$r, 'autoload']);
unset($r);
spl_autoload_unregister('spl_autoload_call');
$loaders = spl_autoload_functions();
echo "reregistered count: ", count($loaders), "\n";
echo "reregistered first: ", $loaders[0][0]->label, "\n";
unset($loaders);
class_exists('GH25723ReregCheck');
spl_autoload_unregister('spl_autoload_call');

class ThrowingLoader {
    public function autoload(string $class): void {}
    public function __destruct() {
        throw new RuntimeException('loader dtor failed');
    }
}

Target::$dtors = 0;
spl_autoload_register([new ThrowingLoader(), 'autoload']);
spl_autoload_register([new Target(), 'autoload']);
try {
    spl_autoload_unregister('spl_autoload_call');
} catch (Throwable $e) {
    echo "exception: ", $e::class, ": ", $e->getMessage(), "\n";
}
echo "exception sibling dtors: ", Target::$dtors, "\n";
echo "exception remaining count: ", count(spl_autoload_functions()), "\n";

class ActiveCleaner {
    public function autoload(string $class): void {
        echo "active-clean\n";
        spl_autoload_unregister('spl_autoload_call');
    }
}

spl_autoload_register([new ActiveCleaner(), 'autoload']);
spl_autoload_register([new SelfUnregister(), 'autoload']);
class_exists('GH25723MissingClass');
echo "after active-clean count: ", count(spl_autoload_functions()), "\n";
echo "done\n";
?>
--EXPECTF--
Deprecated: spl_autoload_unregister(): Using spl_autoload_call() as a callback for spl_autoload_unregister() is deprecated, to remove all registered autoloaders, call spl_autoload_unregister() for all values returned from spl_autoload_functions() in %s on line %d
self-unregister count: 0
self-unregister result: bool(false)
sibling-unregister result: bool(false)
closure-recursive count: 0
closure-recursive: bool(true)
closure-recursive target dtors: 1
trampoline-unregister: bool(false)
reregistered count: 11
reregistered first: prepended
loaded by prepended: GH25723ReregCheck
exception: RuntimeException: loader dtor failed
exception sibling dtors: 1
exception remaining count: 0
active-clean
self-unregister count: 0
self-unregister result: bool(false)
after active-clean count: 0
done
