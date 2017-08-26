<?php
ini_set('memory_limit', '-1');
require_once './app/CompareFiles.php';
//new CompareFiles($argv);
new CompareFiles(array('C:\wamp\www\clickandboat\cap\text1.txt', 'C:\wamp\www\clickandboat\cap\text2.txt'));

