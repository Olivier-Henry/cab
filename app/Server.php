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
            try {
                if (is_array(json_decode($msg, true))) {
                    $this->paths = $this->analysePaths(json_decode($msg, true));
                }
                new CompareFiles($this->paths, $client);
            } catch (\Exception $exc) {
                file_put_contents("/Library/Server/Web/Data/Sites/Default/cab/test.log", $exc);
                $client->send(json_encode([$exc->getMessage()]));
                break;
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

    protected function analysePaths($paths) {

        if (!is_array($paths) || !count($paths) === 2) {
            throw new \Exception("invalid arguments: two paths are needed");
        }
        if (!file_exists($paths[0])) {
            throw new \Exception("File " . basename($paths[0]) . " doesn't exist at " . $paths[0]);
        }
        if (!file_exists($paths[1])) {
            throw new \Exception("File " . basename($paths[1]) . " doesn't exist at " . $paths[1]);
        }
        
        return $paths;
    }

}
