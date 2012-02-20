<?php
namespace ZLogger;

class Decoder
{
	public $cb;

	function __construct($cb = null)
	{
		$this->cb = $cb ?:
			function_exists('bson_decode')  
				? 'bson_decode' : 
			function_exists('json_decode')
				? function($json) { return json_decode($json,true); } : 'unserialize';
	}

	function __invoke($string)
	{
		$data = call_user_func( $this->cb , $string );
		if( $data === null || $data === false ) {
			throw new Exception( __CLASS__ . ': Can not decode data');
		}
		return $data;
	}

    function decode($string)
    {
		$data = call_user_func( $this->cb , $string );
		if( $data === null || $data === false ) {
			throw new Exception( __CLASS__ . ': Can not decode data');
		}
		return $data;

    }
}


