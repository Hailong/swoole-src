--TEST--
swoole_server: dispatch_mode = 7 [stream]
--SKIPIF--
<?php require __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php declare(strict_types = 1);
require __DIR__ . '/../include/bootstrap.php';

use Swoole\Coroutine\Client;
use Swoole\Timer;
use Swoole\Event;
use Swoole\Server;

$pm = new SwooleTest\ProcessManager;
$pm->parentFunc = function ($pid) use ($pm) {
    $client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
    if (!$client->connect('127.0.0.1', $pm->getFreePort(), 0.5, 0)) {
        echo "Over flow. errno=" . $client->errCode;
        die("\n");
    }

    $data = array(
        'name' => __FILE__,
        'sid' => 1000236,
        'content' => str_repeat('A', 8192 * rand(1, 3)),
    );

    $_serialize_data = serialize($data) . "\r\n\r\n";

    $chunk_size = 2048;
    $len = strlen($_serialize_data);
    $chunk_num = intval($len / $chunk_size) + 1;
    for ($i = 0; $i < $chunk_num; $i++) {
        if ($len < ($i + 1) * $chunk_size) {
            $sendn = $len - ($i * $chunk_size);
        } else {
            $sendn = $chunk_size;
        }
        $client->send(substr($_serialize_data, $i * $chunk_size, $sendn));
        usleep(rand(100, 1000));
    }
    echo $client->recv();
    $pm->kill();
};

$pm->childFunc = function () use ($pm) {
    $serv = new Server('127.0.0.1', $pm->getFreePort(), SWOOLE_PROCESS);
    $serv->set(array(
        'package_eof' => "\r\n\r\n",
        'open_eof_check' => true,
        'open_eof_split' => true,
        'dispatch_mode' => 3,
        'package_max_length' => 1024 * 1024 * 2, //2M
        "worker_num" => 1,
        'log_file' => '/dev/null',
        "reload_async" => true,
    ));
    $serv->on("WorkerStart", function (Server $serv) use ($pm) {
        $pm->wakeup();
    });
    $serv->on('receive', function (Server $serv, $fd, $rid, $data) {
        $_data = unserialize(rtrim($data));
        if ($_data and is_array($_data) and $_data['sid'] == 1000236) {
            $serv->send($fd, "SUCCESS\n");
        } else {
            $serv->send($fd, "ERROR\n");
        }
    });
    $serv->start();
};

$pm->childFirst();
$pm->run();
?>
--EXPECT--
SUCCESS
