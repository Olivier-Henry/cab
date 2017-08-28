<?php

namespace App;

/**
 * Description of CompareFiles
 *
 * @author olivier
 */
class CompareFiles {

    private $filesPath;
    private $phrases;
    private $punctuation = array(".", "?", "!", ":", ";");
    private $client;

    public function __construct($filePaths, $client = null) {
        
        $this->client = $client;
        $this->filesPath = $filePaths;
        $this->filesPath = $this->setSmallestFileFirst();
        $this->readFile(0);
        $this->readFile(1);
    }

    /**
     * Reverse the files paths to get smallest file size as first index
     * @return array with smallest file size path as first index;
     */
    protected function setSmallestFileFirst() {
        return filesize($this->filesPath[0]) > filesize($this->filesPath[1]) ? array_reverse($this->filesPath) : $this->filesPath;
    }

    /**
     * read and compare phrases in files, and send it to output
     * @param int $position the position in $filesPath
     * @throws Exception if the file cannot be read
     */
    protected function readFile($position) {

        $lastChunkResidue = '';
        $pool = [];
       

        if ($handle = fopen($this->filesPath[$position], 'rb')) {
            
             $found = false;
             
             
            while (!feof($handle)) {

                $buffer = $lastChunkResidue . fread($handle, 8192);
                $strlen = strlen($buffer);
                $index = 0;
               

                for ($i = 0; $i < $strlen; $i++) {
                    if (in_array($buffer{$i}, $this->punctuation)) {
                        if ($buffer{$i} === "." && $i > 0 && $i < $strlen - 1 && is_numeric($buffer{$i - 1}) && is_numeric($buffer{$i + 1})) {
                            continue;
                        }
                        $plen = $i - $index + 1;
                        $p = str_replace(PHP_EOL, '', substr($buffer, $index, $plen));
                        $index = $i + 1;
                        $hash = md5($p);
                        if ($position === 0) {
                            $this->phrases[$hash] = $plen;
                            continue;
                        }
                        if (isset($this->phrases[$hash]) && $this->phrases[$hash] === $plen) {
                            echo $p . PHP_EOL;
                            $pool[] = $p;
                            $found = true;
                            if (!is_null($this->client) && count($pool) === 100) {
                                $this->client->send(json_encode($pool));
                                $pool = [];
                            }
                            unset($this->phrases[$hash]);
                        }
                    }
                }

                if ($index !== $strlen - 1) {
                    $lastChunkResidue = str_replace(PHP_EOL, '', substr($buffer, $index, $strlen - 1 - $index));
                }
            }

            if (!is_null($this->client) && count($pool)) {
                $this->client->send(json_encode($pool));
                $pool = [];
            }
            
            if($found === false && $position > 0){
                echo "No duplicate phrase was found";
                
                if(!is_null($this->client)){
                    $this->client->send(json_encode(["No duplicate phrase was found"]));
                }
            }
        } else {
            throw new \Exception("File " . basename($this->filesPath[$position]) . "cannot be read");
        }
    }

}
