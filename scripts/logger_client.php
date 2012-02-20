<?php
require 'tests/bootstrap.php';

$queue = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_REQ, "MySock1");
$queue->connect("tcp://127.0.0.1:5555");

$encoder = new ZLogger\Encoder;

// Assign socket 1 to the queue, send and receive
// var_dump($queue->send("hello there!"));

$text = $encoder(array( 'message' => 'text' ));
$queue->send( $text );
