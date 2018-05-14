<?php
namespace IO;

/**
 * Class FileWriter
 * @package IO
 *
 * This class is a simple wrapper around fopen intended for writing chunks of data to a file
 */
class FileWriter {

    /**
     * @var $fileHandle
     */
    protected $fileHandle;

    /**
     * FileWriter constructor.
     */
    public function __construct() {

    }

    /**
     * Open a file for writing.
     * @param $fileName
     * @throws Exception when an error occurs opening a file handle to the specified $fileName
     */
    public function openFile($fileName) {
        $this->fileHandle = fopen($fileName, 'w');

        if($this->fileHandle == false) {
            throw new Exception('There was an error writing the file ' . $fileName);
        }
    }

    /**
     * Call to write a piece of data to the end of the file specified to openFile
     * @param $data
     */
    public function writePart($data) {
        fwrite($this->fileHandle, $data);
    }

    /**
     * Call when finished writing to the file
     */
    public function closeFile() {
        fclose($this->fileHandle);
    }
}