<?php
require_once('Http/FileDownloader.php');
require_once('IO/FileWriter.php');

use \IO\FileWriter;
use \Http\FileDownloader;

/**
 * Class App
 *
 * This app class will download a file in pieces. Call it from your own CLI wrapper script
 * @example
 * <code>
 *     $fileDownloader = new FileDownloader();
 *     $fileWriter = new FileWriter();
 *     $theApp = new App($fileWriter, $fileDownloader);
 *     if(!$theApp->isCli()) {
 *         exit();
 *     }
 *     else {
 *        $theApp->run();
 *     }
 * </code>
 */
class App {

    /**
     * MiB Unit Definition
     */
    const MiB = 1024;

    /**
     * Application Usage Example
     */
    const USAGE = 'Usage: php App.php --url=http://example.com/fileName.ext [--outputFileName=otherFileName.ext]';

    /**
     * Error message for required parameter missing condition
     */
    const REQUIRED_PARAMETER_URL_MISSING = 'Missing required parameter --url';

    /**
     * @var FileDownloader
     */
    protected $fileDownloader;

    /**
     * @var FileWriter
     */
    protected $fileWriter;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var string
     */
    protected $outputFileName;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $totalMiB;

    /**
     * @var int
     */
    protected $maxSize;

    /**
     * @var
     */
    protected $destinationFileName;

    /**
     * @var
     */
    protected $data;

    /**
     * @var int
     */
    protected $rangeFloor;

    /**
     * @var int
     */
    protected $rangeCeiling;

    /**
     * @var int
     */
    protected $rangeIncrement;

    /**
     * App constructor.
     * @param FileWriter $fileWriter
     * @param FileDownloader $fileDownloader
     */
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

    /**
     * @param FileWriter $fileWriter
     */
    public function setFileWriter(FileWriter $fileWriter) {
        $this->fileWriter = $fileWriter;
    }

    /**
     * @param FileDownloader $fileDownloader
     */
    public function setFileDownloader(FileDownloader $fileDownloader) {
        $this->fileDownloader = $fileDownloader;
    }

    /**
     * Determines if the end user passed a special output file name to use
     * @return bool
     */
    public function hasOutputFileName() {
        return !empty($this->options['outputFileName']);
    }

    /**
     * Gets the output file name from the end user passed options if any was set
     * @return mixed
     */
    public function getOutputFileName() {
        return $this->options['outputFileName'];;
    }

    /**
     * Reads the command line options using getopt
     * @return array
     */
    public function readOptions() {
        return getopt(null, ['url:', 'outputFileName::']);
    }

    /**
     * Determines if we're running in CLI mode
     * @return bool
     */
    public function isCli() {
        return php_sapi_name() == 'cli';
    }

    /**
     * Runs the application. Effectively main / entry point
     */
    public function run() {
        // Get options from end user
        $this->options = $this->readOptions();

        // Exit if url isn't set
        if(!empty($this->options['url'])) {
            $this->url = $this->options['url'];
        }
        else {
            echo self::REQUIRED_PARAMETER_URL_MISSING . PHP_EOL;
            echo self::USAGE . PHP_EOL;
            exit(0);
        }

        // Set output file equal to basename of url if none specified
        if($this->hasOutputFileName()) {
            $this->outputFileName = $this->getOutputFileName();
        }
        else {
            $this->outputFileName = basename($this->url);
        }

        // Set up our file handle for writing
        try {
            $this->fileWriter->openFile($this->outputFileName);
        }
        catch(Exception $e) {
            echo "Could not open file " . $this->outputFileName;
            exit(1);
        }

        // Initialize the file downloader and point it to our file
        $this->fileDownloader->initialize();
        $this->fileDownloader->setFileUrl($this->url);

        // Get file in single MiB increments until maxSize is reached
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