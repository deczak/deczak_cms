<?php

class CSingleton
{
	public static function instance()
	{
		static $instance = false;
		if( $instance === false )
			$instance = new static();
		return $instance;
	}

	public static function GET()
	{
		return static::instance();
	}

	private function __construct()	{}
	private function __clone() 		{}
	#private function __sleep() 		{}
	#private function __wakeup() 	{}
}