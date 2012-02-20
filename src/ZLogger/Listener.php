<?php
namespace ZLogger;
use ZMQ;
use ZMQContext;
use Exception;
use ZMQSocketException;

class Listener
{
    public $bind;

    public $encoder;

    public $decoder;

    public $callback;

    function __construct($bind = 'tcp://127.0.0.1:5555', $options = array() )
    {
        $this->bind = $bind;
        $this->encoder = new Encoder( @$options['encoder'] );
        $this->decoder = new Decoder( @$options['decoder'] );

        if( ! extension_loaded('zmq') )
            dl('zmq');
        if( ! extension_loaded('libevent') )
            dl('libevent');
    }

    function recv($cb)
    {
        $this->callback = $cb;
    }


    function listen()
    {
        // create zeromq request/reply 
        $context = new ZMQContext();
        $rep = $context->getSocket(ZMQ::SOCKET_REP);
        $rep->bind( $this->bind );

        try {
            while(true) {
                $msg = $rep->recv();
                $data = $this->decoder->decode($msg);
                call_user_func_array( $this->callback , array(
                    $data,
                    $rep
                ));
                $rep->send('1');
            }
        } 
        catch( ZMQSocketException $e ) {
            $rep->send( $e->getMessage() );
        }
        catch( Exception $e ) {
            $rep->send( $e->getMessage() );
        }
    }
}

