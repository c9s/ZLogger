<?php
require 'tests/bootstrap.php';

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

    function __construct( $options = array() )
    {
        $this->bind = @$options['bind'] ?: 'tcp://127.0.0.1:5555';
        $this->socketId = @$options['socketId'] ?: 'LoggerSock';

        $this->encoder = new ZLogger\Encoder;

        $this->context = new ZMQContext();

        $this->maxRetry = @$options['retry'] ?: 3;

        $this->requestTimeout  = @$options['timeout'] ?: 1000;

        $this->socket = $this->newSocket( $this->context );
    }

    function newSocket($context)
    {
        $socket = new ZMQSocket($context, ZMQ::SOCKET_REQ, $this->socketId );
        $socket->connect( $this->bind );
        $socket->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);
        return $socket;
    }

    function send($data)
    {
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
                        printf ("I: server replied OK (%s)%s", $reply, PHP_EOL);
                        // $retries_left = $this->maxRetry;
                        $expect_reply = false;
                    } else {
                        printf ("E: malformed reply from server: %s%s", $reply, PHP_EOL);
                    }
                } else if(--$retries_left == 0) {
                    // throw exception
                    echo "E: server seems to be offline, abandoning", PHP_EOL;
                    break;
                } else {
                    echo "W: no response from server, retrying…", PHP_EOL;

                    try {
                        //  Old socket will be confused; close it and open a new one
                        $socket = $this->newSocket($this->context);
                        //  Send request again, on new socket
                        $socket->send( $payload );
                    } catch( Exception $e ) {  }

                }
            }
        } 
        catch ( ZMQSocketException $e ) {
            die( $e->getMessage() );
        }
    }

    function info($message)
    {
        return $this->send(array( 
            'type' => 'I',
            'message' => $message,
        ));
    }


}


// Assign socket 1 to the queue, send and receive
// var_dump($queue->send("hello there!"));
$logger = new Client;

$i = 0;
while(1) {
    $i++;
    $logger->info( "Hello $i" );
    sleep(1);
}