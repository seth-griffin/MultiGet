<?php

class FileWriter {

    protected $fileHandle;

    public function __construct() {
    }

    public function openFile($fileName) {
        $this->fileHandle = fopen($fileName, 'w');

        if($this->fileHandle == false) {
            throw new Exception('There was an error writing the file ' . $fileName);
        }
    }

    public function writePart($data) {
        fwrite($this->fileHandle, $data);
    }

    public function closeFile() {
        fclose($this->fileHandle);
    }
}