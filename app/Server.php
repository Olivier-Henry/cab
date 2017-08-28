<?php

namespace App;

use App\CompareFiles;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Server implements MessageComponentInterface {

    protected $clients;
    protected $paths;
    protected $callback;

    public function __construct($paths, $callback) {
        $this->clients = new \SplObjectStorage;
        $this->paths = $paths;
        $this->callback = $callback;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
                , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            if( $this->analysePaths(json_decode($msg, true), $client)) {
                $this->paths =json_decode($msg, true);
                new CompareFiles($this->paths, $client);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        $conn->close();
        $this->close();
        echo "Connection {$conn->resourceId} has been disconnected and closed\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
        $this->close();
    }

    protected function close() {
        call_user_func($this->callback);
    }

    protected function analysePaths($paths, $client) {

        if (!is_array($paths) || !count($paths) === 2) {
            $client->send(["invalid arguments: two paths are needed"]);
        }
        if (!file_exists($paths[0])) {
            $client->send(["File " . basename($paths[0]) . " doesn't exist at " . $paths[0]]);
        }
        if (!file_exists($paths[1])) {
            $client->send(["File " . basename($paths[1]) . " doesn't exist at " . $paths[1]]);
        }

        return true;
    }

}
