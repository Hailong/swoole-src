--TEST--
swoole_coroutine/cancel: waitSignal
--SKIPIF--
<?php require __DIR__ . '/../../include/skipif.inc'; ?>
--FILE--
<?php declare(strict_types = 1);
require __DIR__ . '/../../include/bootstrap.php';

use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;
use Swoole\Coroutine;
use Swoole\Coroutine\System;

run(function () {
    $cid = Coroutine::getCid();
    go(function () use ($cid) {
        System::sleep(0.002);
        Assert::true(Coroutine::cancel($cid));
    });
    $retval = System::waitSignal(SIGTERM);
    echo "Done\n";
    Assert::eq($retval, false);
    Assert::eq(swoole_last_error(), SWOOLE_ERROR_CO_CANCELED);
});

?>
--EXPECT--
Done
