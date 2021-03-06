<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Server;

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';


array_shift($argv);

if (!is_array($argv) || !count($argv) === 2) {
    throw new Exception("invalid arguments: two paths are needed");
}
if (!file_exists($argv[0])) {
    throw new Exception("File " . basename($argv[0]) . " doesn't exist at " . $argv[0]);
}
if (!file_exists($argv[1])) {
    throw new Exception("File " . basename($argv[1]) . " doesn't exist at " . $argv[1]);
}



$server = IoServer::factory(
                new HttpServer(
                    new WsServer(
                        new Server($argv, 'stopCallback')
                    )
                ), 3030
);

$server->run();

// when loop completed, run this function
function stopCallback() {
    $server->loop->stop();
}

