<?php
namespace ZLogger;
use ZMQ;
use ZMQContext;

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
    }

    function recv($cb)
    {
        $self = $this;
        $self->callback = function($fd,$events,$arg) use($self,$cb) {
            if($arg[0]->getsockopt (ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN) {
                $string = $arg[0]->recv();
                $cb( $self->decoder($string,$arg));
            }
        };
    }

    function listen()
    {
        // initialize event and zmq context

        // create base and event
        $base  = event_base_new();
        $event = event_new();

        // Allocate a new context
        $context = new ZMQContext();

        // Create sockets
        $rep = $context->getSocket(ZMQ::SOCKET_REP);

        // Connect the socket
        $rep->bind( $this->bind );


        // Get the stream descriptor
        $fd = $rep->getsockopt(ZMQ::SOCKOPT_FD);

        // set event flags
        event_set($event, $fd, EV_READ | EV_PERSIST, $this->callback , array($rep, $base));

        // set event base
        event_base_set($event, $base);

        // enable event
        event_add($event);

        // start event loop
        event_base_loop($base);
    }
}

