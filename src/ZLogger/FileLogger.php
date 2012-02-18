<?php
namespace ZLogger;
use ZMQ;
use ZMQContext;
use Exception;

class FileLogger
{


    const default_host = '127.0.0.1';

    const default_port = 5555;

    public $sizeLimit;

    /**
     * default directory 
     */
    public $directory;

    /**
     * default file format.
     */
    public $filepath;


    /**
     * content lines
     */
    public $lines = 0;

    /**
     * @var file resource pointer
     */
    public $fp;

    public $host;

    public $port;

    /**
     * zeromq listener 
     */
    public $listener;

    function __construct($options = array())
    {

        $this->sizeLimit = @$options['size_limit'];

        $this->directory = @$options['directory'];

        $this->host = @$options['host'] ?: self::default_host;

        $this->port = @$options['port'] ?: self::default_port;

        // use php.strftime format
        $this->filepath = @$options['path'];

        $this->listener = @$options['listener'] ?: 
                new Listener('tcp://' . $this->host . ':' . $this->port );
    }

    public function getLogFilepath()
    {
        if( $this->filepath )
            return strftime( $this->filepath , time() );
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
        $self = $this;
        // initialize event and zmq context
        $this->fp = $this->openLogFile();
        $this->listener->recv(function($data,$arg) use ($self) {
            $self->lines++;
            fwrite( $self->fp , $data['message'] );
        });
        $this->listener->listen();
    }
}




