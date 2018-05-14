<?php
namespace Http;

/**
 * Class FileDownloader
 * @package Http
 *
 * This class is a simple wrapper around the php-curl library used for the purpose of downloading a file in chunks
 */
class FileDownloader
{
    /**
     * @var $ch - The curl file handle
     */
    protected $ch;

    /**
     * @var $error - Error messages returned by curl following curl_exec in the event curl_exec returns boolean false
     */
    protected $error;

    /**
     * FileDownloader constructor.
     */
    public function __construct() {

    }

    /**
     * Call this first after creating an instance of the class
     */
    public function initialize() {
        $this->ch = curl_init();
    }

    /**
     * Call this prior to calling getFilePart
     * @param $url
     */
    public function setFileUrl($url) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
    }

    /**
     * Gets a file part using the HTTP Range header
     * @param $rangeMin
     * @param $rangeMax
     * @return mixed - Returns a chunk of the file data
     * @throws Exception - In the event that there is an error fetching data
     */
    public function getFilePart($rangeMin, $rangeMax) {
        $range = $rangeMin . '-' . ($rangeMax - 1);
        curl_setopt($this->ch, CURLOPT_RANGE, $range);
        $data = curl_exec ($this->ch);

        if($data === false) {
            throw new Exception(curl_error($this->ch));
        }

        return $data;
    }

    /**
     * Call this after all file parts are fetched
     */
    public function close() {
        curl_close($this->ch);
    }
}