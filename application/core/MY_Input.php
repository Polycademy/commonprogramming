<?php

class MY_Input extends CI_Input{

	//extract json input data from input stream
	//note that XSS clean results in an extra semi-colon if the string uses ampersand '&' by itself
	//this may result in problems, so try different methods if you need to have '&' unmolested
	public function json($index = false, $xss_clean = false, $return_as_object = false){
	
		if($return_as_object){
			$input_data = json_decode(trim(file_get_contents('php://input')));
		}else{
			$input_data = json_decode(trim(file_get_contents('php://input')), true);
		}
		
		if($xss_clean){
			if(is_array($input_data)){
				foreach($input_data as &$value){
					$value = $this->security->xss_clean($value);
				}
			}elseif(is_object($input_data)){
				foreach($input_data as $key => $value){
					$input_data->$key = $this->security->xss_clean($value);
				}
			}
		}
		
		if($index){
			if(is_array($input_data)){
				return $input_data[$index];
			}elseif(is_object($input_data)){
				return $input_data->$index;
			}
		}
		
		return $input_data;
		
	}
	
	//extracts information coming from stdin, and you can specify the type to be json encoded or php encoded (serialisation) or false encoding
	public function stdin($index = false, $xss_clean = false, $type = 'json', $return_as_object = false){
	
		$input_data = '';
		while(FALSE !== ($line = fgets(STDIN))){
			$input_data .= $line;
		}		
		
		switch($type){
			case 'json':
				if($return_as_object){
					$input_data = json_decode(trim($input_data));
				}else{
					$input_data = json_decode(trim($input_data), true);
				}
				break;
			case 'php':
				$input_data = unserialize($input_data);
				break;
		}
		
		//not if $type is false
		if($xss_clean){
			if(is_array($input_data)){
				foreach($input_data as &$value){
					$value = $this->security->xss_clean($value);
				}
			}elseif(is_object($input_data)){
				foreach($input_data as $key => $value){
					$input_data->$key = $this->security->xss_clean($value);
				}
			}else{
				$input_data = $this->security->xss_clean($input_data);
			}
		}
		
		if($index){
			if(is_array($input_data)){
				return $input_data[$index];
			}elseif(is_object($input_data)){
				return $input_data->$index;
			}
		}
		
		return $input_data;
	
	}

}