<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Server;

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!is_array($argv) || !count($argv) === 3) {
    throw new Exception("invalid arguments: two paths are needed");
}
if (!file_exists($argv[0])) {
    throw new Exception("File " . basename($argv[1]) . " doesn't exist at " . $argv[1]);
}
if (!file_exists($argv[1])) {
    throw new Exception("File " . basename($argv[2]) . " doesn't exist at " . $argv[2]);
}

array_shift($argv);

$server = IoServer::factory(
                new HttpServer(
                    new WsServer(
                        new Server($argv)
                    )
                ), 3030
);

$server->run();

