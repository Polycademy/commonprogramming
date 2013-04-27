<?php

//takes an array of validation errors, and changes their key to represent {prefixFormInputName}
if(!function_exists('output_message_mapper')){
	function output_message_mapper($messages, $prefix = ''){
	
	
		foreach($messages as $key => $value){

			$key = (!empty($prefix)) ? camelize($prefix . '_' . $key) : camelize($key);
			
			$new_messages[$key] = $value;
			
		}

		return $new_messages;
		
	}
}

//this does the opposite of the output_message_mapper, it takes camelcased keys and removes prefix and turns them into snake_case
//camel to snake!
if(!function_exists('input_message_mapper')){
	function input_message_mapper($messages, $prefix = ''){
		
		foreach($messages as $key => $value){
		
			if(!empty($prefix)){
				if(substr($key, 0, strlen($prefix)) == $prefix){
					$key = substr($key, strlen($prefix), strlen($key));
				}
			}
			
			$key = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
			
			$new_messages[$key] = $value;
			
		}

		return $new_messages;
		
	}
}