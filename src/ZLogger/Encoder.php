<?php

namespace ZLogger;
class Encoder
{
	public $cb;

	function __construct($cb = null)
	{
		$this->cb = $cb ?:
			function_exists('bson_encode')  
				? 'bson_encode' : 
			function_exists('json_encode')
				? 'json_encode' : 'serialize';
	}

	function __invoke($string)
	{
		return call_user_func( $this->cb , $string );
	}
}


