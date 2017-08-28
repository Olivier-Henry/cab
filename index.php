<?php

ini_set('memory_limit', '-1');
set_time_limit(0);

use App\CompareFiles;
use Symfony\Component\Process\PhpExecutableFinder;

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (isset($argv)) {
    
    array_shift($argv);
    $filePaths[] = $argv[0];
    $filePaths[] = $argv[1];
    
} else if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
    
    $filePaths = json_decode(file_get_contents('php://input'));
    
}


$phpFinder = new PhpExecutableFinder;
if (!$phpPath = $phpFinder->find()) {
    throw new \Exception('The php executable could not be found, add it to your PATH environment variable and try again');
}



if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
    header($string);

    try {
        $filePaths = fileErrors($filePaths);
    } catch (Exception $ex) {
        header('Content-Type: application/json');
        echo json_encode([$ex->getMessage()]);
        return false;
    }

    $compareServer = '"' . __DIR__ . DIRECTORY_SEPARATOR . 'compare.php" "' . $filePaths[0] . '" "' . $filePaths[1] . '"';

    exec('"' . $phpPath . '" ' . $compareServer);
    return true;
}


fileErrors($filePaths);
new CompareFiles($filePaths);

function fileErrors($paths) {
    if (!is_array($paths) || !count($paths) === 2) {
        throw new Exception("invalid arguments: two paths are needed");
    }
    if (!file_exists($paths[0])) {
        throw new Exception("File " . basename($paths[0]) . " doesn't exist at " . $paths[0]);
    }
    if (!file_exists($paths[1])) {
        throw new Exception("File " . basename($paths[1]) . " doesn't exist at " . $paths[1]);
    }

    return $paths;
}
