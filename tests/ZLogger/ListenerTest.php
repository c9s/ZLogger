<?php

class ListenerTest extends PHPUnit_Framework_TestCase
{
	function test()
	{
		$count = 0;
		$listener = new ZLogger\Listener('tcp://127.0.0.1:5555');
		$listener->listen(function($fd,$events,$arg) use ( &$count) {
			$count++;
			event_base_loopexit($arg[1]);
		});

		$queue = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_REQ, "MySock1");
		$queue->connect("tcp://127.0.0.1:5555");

		// Assign socket 1 to the queue, send and receive
		// var_dump($queue->send("hello there!")->recv());
		$queue->send("data");
		ok( $count );
	}
}


