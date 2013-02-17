<?php

class Template{

	//partial static, data should be an array, loop by default is false, buffer by default is false (by default will return data...
	//call this like Template::partial('header', $data)
	public static function partial($partial, array $data, $loop = false, $buffer = false){	
	
		//if no _partial, then we append it
		if(strpos($partial, '_partial') === false){
			$partial .= '_partial';
		}
	
		$output = '';
		
		//if we're asked to loop the results
		if($loop){
		
			foreach($data as $row){
			
				//any looped partials will have to use the row variable, or else no looping for you!
				$output .= get_instance()->load->view('partials/' . $partial, array('row' => $row), true);
			
			}
		
		}else{
		
			$output = get_instance()->load->view('partials/' . $partial, $data, true);
		
		}
		
		if($buffer){
		
			return $output;
		
		}else{
		
			echo $output;
			
		}
		
	}
	
	//$view is the path to the view based on the current controller, $data is the data we pass and $layout is the path to the layout we shall use
	//use like Template::compose('index', $data, 'default');
	//or Template::compose(false, $data, 'json');
	public static function compose($view, array $data, $layout = 'default'){
	
		//auto detecting layout
		if(strpos($layout, '_layout') === false){
			$layout .= '_layout';
		}
	
		//if $view is false/empty, we pass straight the data straight to layout through yield (such as json data)
		if(empty($view)){
		
			get_instance()->load->view('layouts/' . $layout, array('yield' => $data));
		
		}else{
		
			//auto appending _view
			if(strpos($view, '_view') === false){
				$view .= '_view';
			}
			
			//auto add directory and controller to the view (views are the method names, which are stored in the controllers)
			$view = strtolower(get_instance()->router->fetch_directory() . get_instance()->router->fetch_class() . '/' . $view);
			
			//acquire the yield
			$data['yield'] = get_instance()->load->view($view, $data, true);
			
			//put the rest of the data into the layout!
			//layout now can call yield, along with all the other data (such as $data['header'] and $data['footer'])
			get_instance()->load->view('layouts/' . $layout, $data);
		
		}
	
	}

}