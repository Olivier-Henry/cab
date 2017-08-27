<?php

ini_set('memory_limit', '-1');
set_time_limit(0);

use App\CompareFiles;
use Symfony\Component\Process\PhpExecutableFinder;

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if(isset($argv)){
    $filePaths[]  = $argv[1];
    $filePaths[]  = $argv[2];
}else if(array_key_exists('REQUEST_METHOD', $_SERVER)){
   $filePaths = json_decode(file_get_contents('php://input'));
}

if (!is_array($filePaths) || !count($filePaths) === 2) {
    throw new Exception("invalid arguments: two paths are needed");
}
if (!file_exists($filePaths[0])) {
    throw new Exception("File " . basename($filePaths[0]) . " doesn't exist at " . $filePaths[0]);
}
if (!file_exists($filePaths[1])) {
    throw new Exception("File " . basename($filePaths[1]) . " doesn't exist at " . $filePaths[1]);
}

$phpFinder = new PhpExecutableFinder;
if (!$phpPath = $phpFinder->find()) {
    throw new \Exception('The php executable could not be found, add it to your PATH environment variable and try again');
}



if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
    
    $compareServer = '"' . __DIR__ . DIRECTORY_SEPARATOR . 'compare.php" "' . $filePaths[0] . '" "' .$filePaths[1] .'"';
   
    exec('"' . $phpPath . '" ' . $compareServer . ' > CompareServer.log');
    return true;
}



//new CompareFiles($argv);
new CompareFiles($filePaths);
