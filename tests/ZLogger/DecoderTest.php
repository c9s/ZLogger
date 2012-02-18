<?php

class DecoderTest extends PHPUnit_Framework_TestCase
{
	function test()
	{
		$decoder = new ZLogger\Decoder(function($json){ return json_decode($json,true); });
		ok( $decoder );

		$data = $decoder( json_encode(array( 'msg' => 'text' )) );
		ok( $data['msg'] );


	}

	function testEncoder()
	{
		$encoder = new ZLogger\Encoder('json_encode');
		$data = $encoder(array('msg' => 2));
		ok( $data );
	}

	function testEncodeDecode()
	{
		$encoder = new ZLogger\Encoder;
		$decoder = new ZLogger\Decoder;
		$data = $decoder( $encoder(array( 'msg' => 123 )) );
		ok( $data['msg'] );
		is( 123, $data['msg'] );
	}
}




