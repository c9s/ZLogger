<?php
namespace ZLogger;
use ZMQ;

class FileLogger
{
    public $sizeLimit;

    public $directory;

    public $filepath;

    /* zeromq context */
    public $context;

    /* event base object */
    public $base;


    /**
     * @var Array event object
     */
    public $events = array();


    /**
     * content lines
     */
    public $lines = 0;

    /**
     * @var file resource pointer
     */
    public $fp;


    function __construct($options = array())
    {
        if( ! extension_loaded('zmq') )
            dl('zmq');
        if( ! extension_loaded('event') )
            dl('event');

        $this->sizeLimit = @$options['size_limit'];

        $this->directory = @$options['directory'];

        // use php.strftime format
        $this->filepath = @$options['path'];
    }

    public function getLogFilepath()
    {
        if( $this->filepath )
            return strftime( $this->filenameFormat, time() );
        throw new Exception("default filename format is not defined.");
    }

    public function openLogFile()
    {
        $filepath = $this->getLogFilepath();
        $dir = dirname( $filepath );
        if( ! file_exists($dir) )
            mkdir( $dir , 0755 , true );
        $fp = fopen( $filepath , 'a+' );
        return $fp;
    }

    public function onRecv($fd, $events, $arg) {
    {
        /*
        echo "CALLBACK FIRED" . PHP_EOL;
        */
        if($arg[0]->getsockopt (ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN) {

            echo "Got incoming data" . PHP_EOL;
            var_dump ($arg[0]->recv());

            /*
            $arg[0]->send("Got msg $msgs");
            if($msgs++ >= 10) 
                event_base_loopexit($arg[1]);
             */
        }
    }

    // for time-based log filename, close current file resource,
    // re-open with another filename
    public function truncateLog()
    {
        if( $this->fp )
            fclose($this->fp);
        $this->fp = $this->openLogFile();
    }

    public function start()
    {
        $this->fp = $this->openLogFile();

    }
}




