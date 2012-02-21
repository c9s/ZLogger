<?php
namespace ZLogger;
use CLIFramework\Formatter;
use ZMQContext;
use ZMQSocket;
use ZMQPoll;
use ZMQ;
use ZMQSocketException;
use Exception;

class Client 
{
    public $bind;

    public $socketId;


    /* zmq context */
    public $context;

    /* zmq socket */
    public $socket;

    /* data encoder */
    public $encoder;


    /* maxRetry */
    public $maxRetry;

    public $requestTimeout;

    public $console;

    function __construct( $options = array() )
    {
        $this->bind = @$options['bind'] ?: 'tcp://127.0.0.1:5555';
        $this->socketId = @$options['socket_id'] ?: 'LoggerSock';

        $this->encoder = new Encoder;

        $this->context = new ZMQContext();

        $this->maxRetry = @$options['retry'] ?: 3;

        $this->requestTimeout  = @$options['timeout'] ?: 1000;

        $this->socket = $this->newSocket( $this->context );

        /**
         * if we are in command-line mode, we should prompt retry,error info 
         * */
        $this->console = @$options['console'] ?: false;

        if( $this->console )
            $this->formatter = new Formatter;
    }

    function newSocket($context)
    {
        $socket = new ZMQSocket($context, ZMQ::SOCKET_REQ, $this->socketId );
        $socket->connect( $this->bind );
        $socket->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);
        return $socket;
    }

    function consolePrint( $msg, $style )
    {
        echo $this->formatter->format( 
            $msg , $style ) , PHP_EOL;
    }

    function send($data)
    {
        $msg = $data['message'];
        try {
            $payload =  $this->encoder->encode( $data );
            $this->socket->send( $payload );

            $retries_left = $this->maxRetry;

            $read = $write = array();
            $expect_reply = true;
            while($expect_reply) {
                //  Poll socket for a reply, with timeout
                $poll = new ZMQPoll();
                $poll->add($this->socket, ZMQ::POLL_IN);
                $events = $poll->poll($read, $write, $this->requestTimeout );
                
                //  If we got a reply, process it
                if($events > 0) {
                    //  We got a reply from the server, must match sequence
                    $reply = $this->socket->recv();
                    if(intval($reply) == 1) {
                        if( $this->console ) {
                            // printf("I: server replied OK (%s)%s", $reply, PHP_EOL);
                            $this->consolePrint( $data['type'] . ': ' . $msg, 'green' );
                        }
                        $expect_reply = false;
                    } else {
                        if( $this->console )
                            $this->consolePrint( 'E: malformed reply from server: '. $reply, 'red' );
                    }
                } else if($retries_left == 0) {
                    // throw exception
                    if( $this->console ) {
                        $this->consolePrint('E: server seems to be offline, abandoning','red');
                    }
                    break;
                } else {
                    if( $this->console ) {
                        $this->consolePrint('W: no response from server, retrying…' . $retries_left-- ,'yellow' );
                    }

                    try {
                        //  Old socket will be confused; close it and open a new one
                        $this->socket = $this->newSocket($this->context);
                        //  Send request again, on new socket
                        $this->socket->send( $payload );
                    } catch( Exception $e ) {
                        if( $this->console ) {
                            $this->consolePrint( 'E: ' . $e->getMessage() , 'red' );
                        }
                    }

                }
            }
        } 
        catch ( ZMQSocketException $e ) {
            if( $this->console ) {
                $this->consolePrint( 'E: ' . $e->getMessage(), 'red' );
                die();
            }
            else {
                die( $e->getMessage() );
            }
        }
    }



    function debug($message)
    {
        return $this->send(array(
            'type' => 'D',
            'message' => $message,
        ));
    }

    function error($message)
    {
        return $this->send(array(
            'type' => 'E',
            'message' => $message,
        ));
    }

    function warn($message)
    {
        return $this->send(array(
            'type' => 'W',
            'message' => $message,
        ));
    }

    function info($message)
    {
        return $this->send(array( 
            'type' => 'I',
            'message' => $message,
        ));
    }
}


