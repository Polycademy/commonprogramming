<?php

//takes an array of validation errors, and changes their key to represent {prefixFormInputName}
if(!function_exists('output_message_mapper')){
	function output_message_mapper($messages, $prefix = ''){
	
		$array = array_flip($messages);
		
		array_walk(
			$array,
			function(&$value, $key, $prefix){
				//add in prefix
				//snake to camel!
				$value = (!empty($prefix)) ? camelize($prefix . '_' . $value) : camelize($value);
			},
			$prefix
		);
		
		$array = array_flip($array);

		return $array;
		
	}
}

//this does the opposite of the output_message_mapper, it takes camelcased keys and removes prefix and turns them into snake_case
//camel to snake!
if(!function_exists('input_message_mapper')){
	function input_message_mapper($messages, $prefix = ''){
	
		$array = array_flip($messages);
		
		array_walk(
			$array,
			function(&$value, $key, $prefix){
			
				//remove the prefix, if it exists
				if(!empty($prefix)){
					if(substr($value, 0, strlen($prefix)) == $prefix){
						$value = substr($value, strlen($prefix), strlen($value));
					}
				}
				
				//then camel to snake!
				$value = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $value));
			
			},
			$prefix
		);
		
		$array = array_flip($array);

		return $array;
		
	}
}