<?php
// server
class Server {
    function print_line($fd, $events, $arg) {
        static $msgs = 1; 

        echo "CALLBACK FIRED" . PHP_EOL;
        if($arg[0]->getsockopt (ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN) {

            echo "Got incoming data" . PHP_EOL;
            var_dump ($arg[0]->recv());

            $arg[0]->send("Got msg $msgs");
            if($msgs++ >= 10) 
                event_base_loopexit($arg[1]);
        }
    }
}

$server = new Server;
// create base and event
$base = event_base_new();
$event = event_new();

// Allocate a new context
$context = new ZMQContext();

// Create sockets
$rep = $context->getSocket(ZMQ::SOCKET_REP);

// Connect the socket
$rep->bind("tcp://127.0.0.1:5555");

// Get the stream descriptor
$fd = $rep->getsockopt(ZMQ::SOCKOPT_FD);

// set event flags
event_set($event, $fd, EV_READ | EV_PERSIST, array($server,"print_line") , array($rep, $base));

// set event base
event_base_set($event, $base);

// enable event
event_add($event);

// start event loop
event_base_loop($base);
