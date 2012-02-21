<?php
namespace ZLogger;
use ZMQ;
use ZMQContext;
use Exception;

class FileLogger
{
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

    /**
     * zeromq listener 
     */
    public $listener;

    public $quiet = false;

    public $bind = 'tcp://127.0.0.1:5555';

    function __construct($options = array())
    {

        $this->sizeLimit = @$options['size_limit'];

        $this->directory = @$options['directory'];

        // use php.strftime format
        $this->filepath = @$options['path'];

        $this->bind = @$options['bind'] ?: 'tcp://127.0.0.1:5555';

        $this->listener = @$options['listener'] ?: 
                new Listener( $this->bind );
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
        $this->listener->recv(function($data,$rep) use ($self) {
            $self->lines++;
            fwrite( $self->fp , $data['message'] . PHP_EOL );
            if( ! $self->quiet ) {
                echo $data['type'] , ': ' , $data['message'] , PHP_EOL;
            }
        });
        $this->listener->listen();
        fclose($this->fp);
    }
}




