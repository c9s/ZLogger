<?php
namespace ZLogger;
use ZMQ;
use ZMQContext;

class Listener
{
    public $bind;

    public $serializer;

    public $unserializer;

    function __construct($bind = 'tcp://127.0.0.1:5000', $options = array() )
    {
        $this->bind = $bind;
        $this->serializer = @$options['serializer'] ?: 
            function_exists('bson_encode')  ? 'bson_encode' : 'json_encode';
        $this->unserializer = @$options['unserializer'] ?:
            function_exists('bson_decode')  ? 'bson_decode' : 'json_decode';
    }

    function listen($callback)
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

        $self = $this;
        $wrapperCB = function($fd,$events,$arg) use($self,$callback) {
            

        };

        // set event flags
        event_set($event, $fd, EV_READ | EV_PERSIST, $callback , array($rep, $base));

        // set event base
        event_base_set($event, $base);

        // enable event
        event_add($event);

        // start event loop
        event_base_loop($base);
    }
}

