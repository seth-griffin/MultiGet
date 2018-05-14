<?php
require_once('FileDownloader.php');
require_once('FileWriter.php');
$fileDownloader = new FileDownloader();
$fileWriter = new FileWriter();

$theApp = new App($fileWriter, $fileDownloader);

if(!$theApp->isCli()) {
    exit();
}
else {
    $theApp->run();
}

class App {

    const MiB = 1024;
    const USAGE = 'Usage: php App.php --url=http://example.com/fileName.ext [--outputFileName=otherFileName.ext]';
    const REQUIRED_PARAMETER_URL_MISSING = 'Missing required parameter --url';

    protected $fileDownloader;
    protected $fileWriter;

    protected $options;
    protected $outputFileName;
    protected $url;
    protected $totalMiB;
    protected $maxSize;
    protected $destinationFileName;
    protected $data;
    protected $rangeFloor;
    protected $rangeCeiling;
    protected $rangeIncrement;


    public function __construct(FileWriter $fileWriter, FileDownloader $fileDownloader) {
        $this->options = [];
        $this->outputFileName = '';
        $this->url = '';
        $this->totalMiB = 4;
        $this->maxSize = self::MiB * $this->totalMiB;
        $this->rangeFloor = 0;
        $this->rangeCeiling = self::MiB;
        $this->rangeIncrement = self::MiB;
        $this->fileWriter = $fileWriter;
        $this->fileDownloader = $fileDownloader;
    }

    public function setFileWriter(FileWriter $fileWriter) {
        $this->fileWriter = $fileWriter;
    }

    public function setFileDownloader(FileDownloader $fileDownloader) {
        $this->fileDownloader = $fileDownloader;
    }

    public function hasOutputFileName() {
        return !empty($this->options['outputFileName']);
    }

    public function getOutputFileName() {
        return $this->options['outputFileName'];;
    }

    public function readOptions() {
        return getopt(null, ['url:', 'outputFileName::']);
    }

    public function isCli() {
        return php_sapi_name() == 'cli';
    }

    public function run() {
        $this->options = $this->readOptions();

        if(!empty($this->options['url'])) {
            $this->url = $this->options['url'];
        }
        else {
            echo self::REQUIRED_PARAMETER_URL_MISSING . PHP_EOL;
            echo self::USAGE . PHP_EOL;
        }

        if($this->hasOutputFileName()) {
            $this->outputFileName = $this->getOutputFileName();
        }
        else {
            $this->outputFileName = basename($this->url);
        }

        try {
            $this->fileWriter->openFile($this->outputFileName);
        }
        catch(Exception $e) {
            echo "Could not open file " . $this->outputFileName;
            exit(1);
        }

        $this->fileDownloader->initialize();
        $this->fileDownloader->setFileUrl($this->url);

        while(filesize($this->outputFileName) < $this->maxSize) {
            $output = '';
            $output .= 'Fetching '
                . $this->rangeFloor
                . '-'
                . $this->rangeCeiling
                . ' Bytes ('
            ;

            try {
                $data = $this->fileDownloader->getFilePart($this->rangeFloor, $this->rangeCeiling);
            }
            catch(Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                exit(1);
            }

            $this->fileWriter->writePart($data);

            $this->rangeFloor = $this->rangeCeiling;
            $this->rangeCeiling += $this->rangeIncrement;
            clearstatcache();

            $output .= filesize($this->outputFileName)
                . ' total fetched)'
                . PHP_EOL
            ;

            echo $output;
        }

        $this->fileDownloader->close();
        $this->fileWriter->closeFile();
    }
}