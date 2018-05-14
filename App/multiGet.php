<?php
/**
 * An object oriented solution to the problem statement
 */

require_once('App.php');
use \IO\FileWriter;
use \Http\FileDownloader;


$fileDownloader = new FileDownloader();
$fileWriter = new FileWriter();
$theApp = new App($fileWriter, $fileDownloader);

if(!$theApp->isCli()) {
    exit();
}
else {
    $theApp->run();
}