<?php

/*
 
	This file contains different toolkit functions, as example using those with tk::<function> 

 */

class	tk
{
	public static function
	dbug($_data, bool $_returnAsString = false)
	{
		if($_returnAsString) return print_r($_data, true);
		echo '<pre style="border:1px dotted red; padding:10px; margin:10px;">';
		print_r($_data);
		echo '</pre>';
	}
}

class	crypt
{
}

?>