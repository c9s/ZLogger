<?php
require 'tests/bootstrap.php';

// Assign socket 1 to the queue, send and receive
// var_dump($queue->send("hello there!"));
$logger = new ZLogger\Client(array( 
    'socket_id' => 'logger_sock',
    'timeout' => 1000,
    'retry' => 3,
    'console' => true,
));

$i = 0;
while(1) {
    $i++;
    $logger->info( "Hello $i" );
    sleep(1);
}
