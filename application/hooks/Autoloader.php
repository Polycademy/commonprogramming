<?php
 
class Autoloader{
 
    public function __construct(){
 
        //LOADING COMPOSER
        if(file_exists(FCPATH . '/vendor/autoload.php')){
            include_once FCPATH . '/vendor/autoload.php';
        }
 
        //STANDARD AUTOLOADER
        spl_autoload_register(array($this, 'autoload'));
 
        //use vendor/package/Library.php for package related stuff (psr-0 kind of thing) (each package can have its own autoloader and get its own interfaces and abstracts)
        //use Library.php for non-packaged libraries framework specific
        //use abstracts/aLibrary.php for non-packaged abstracts framework specific
        //use interfaces/iLibrary.php for non-packaged interfaces framework specific
        //MY_Library.php will be loaded using $this->load as a CI specific library
 
    }
 
    public function autoload($class){
 
        //PSR-0 autoloader
 
        $library_path = APPPATH . 'libraries/';
        $third_party_path = APPPATH . 'third_party/';
 
        //remove the first ns (\) since libraries already have it
 
        $class = ltrim($class, '\\');
        $file  = '';
        $namespace = '';
 
        if ($last_namespace_pos = strrpos($class, '\\')) {
 
            $namespace = substr($class, 0, $last_namespace_pos);
            $class = substr($class, $last_namespace_pos + 1);
            //replace all backslashes with DIRECTORY_SEPARATOR, it adds one more to the end
            $file = strtr($namespace, '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
 
        }
        
        //replace all class names with (_) with DIRECTORY_SEPARATOR
        $file .= strtr($class, '_', DIRECTORY_SEPARATOR);
 
        if(file_exists($library_path . $file . '.php')){
		
            require_once($library_path . $file . '.php');
            return;
 
        }elseif(file_exists($third_party_path . $file . '.php')){
 
            require_once($third_party_path . $file . '.php');
            return;
 
        }
 
    }
 
}