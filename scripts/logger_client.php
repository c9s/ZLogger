<?php
require 'tests/bootstrap.php';

$client = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_REQ, "MySock1");
$client->connect("tcp://127.0.0.1:5555");
$client->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);


$encoder = new ZLogger\Encoder;

// Assign socket 1 to the queue, send and receive
// var_dump($queue->send("hello there!"));

$i = 0;
while(1) {
    $text = $encoder(array( 'message' => 'text' ));
    echo ++$i . "\n";
    $msg = $client->send( $i );


    $read = $write = array();
    $expect_reply = true;
    while($expect_reply) {
        //  Poll socket for a reply, with timeout
        $poll = new ZMQPoll();
        $poll->add($client, ZMQ::POLL_IN);
        $events = $poll->poll($read, $write, REQUEST_TIMEOUT * 1000);
        
        //  If we got a reply, process it
        if($events > 0) {
            //  We got a reply from the server, must match sequence
            $reply = $client->recv();
            if(intval($reply) == $sequence) {
                printf ("I: server replied OK (%s)%s", $reply, PHP_EOL);
                $retries_left = REQUEST_RETRIES;
                $expect_reply = false;
            } else {
                printf ("E: malformed reply from server: %s%s", $reply, PHP_EOL);
            }
        } else if(--$retries_left == 0) {
            echo "E: server seems to be offline, abandoning", PHP_EOL;
            break;
        } else {
            echo "W: no response from server, retrying…", PHP_EOL;
            //  Old socket will be confused; close it and open a new one
            $client = client_socket($context);
            //  Send request again, on new socket
            $client->send($sequence);
        }
    }

    sleep(1);
}
