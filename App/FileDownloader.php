<?php

class FileDownloader
{
    protected $ch;
    protected $error;

    public function __construct() {

    }

    public function initialize() {
        $this->ch = curl_init();
    }

    public function setFileUrl($url) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
    }

    public function getFilePart($rangeMin, $rangeMax) {
        $range = $rangeMin . '-' . ($rangeMax - 1);
        curl_setopt($this->ch, CURLOPT_RANGE, $range);
        $data = curl_exec ($this->ch);

        if($data === false) {
            throw new Exception(curl_error($this->ch));
        }

        return $data;
    }

    public function getLastError() {
        return $this->error;
    }

    public function close() {
        curl_close($this->ch);
    }
}