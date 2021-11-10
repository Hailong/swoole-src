<?php

/** @not-serializable */
namespace Swoole\Process {
    class Pool {
        public function __construct(int $worker_num, ?int $ipc_type = 0, ?int $msgqueue_key = 0, ?bool $enable_coroutine = 0) {}
        public function set(array $settings): bool {}
        public function on(string $event, callable $callback): bool {}
        public function listen(string $host, ?int $port = 0, ?int $backlog = 2048): bool {}
        public function write(string $data): bool {}
        public function detach(): bool {}
        public function getProcess(?int $worker_id = -1): mixed {}
        public function start(): bool|void {}
        public function stop(): void {}
        public function shutdown(): bool {}
        public function __destruct() {}
    }
}