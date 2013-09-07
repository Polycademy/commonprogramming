<?php

/**
 * Loads secrets into the application. If no secrets are loaded, it will close the application and announce this.
 */
class Secrets{

	public static function load(){
		
		$secrets_path = FCPATH . '/.secrets';
		$secrets_loaded = false;

		//see if "secrets folder" exists
		if(file_exists($secrets_path) AND is_dir($secrets_path)){

			foreach(new DirectoryIterator($secrets_path) as $file){

				//ignore dots and non-php extensions and this file itself
				if($file->isDot() OR $file->getExtension() != 'php' OR $file->getFilename() == 'Secrets.php') continue;
				
				$secrets_loaded = true;

				include_once($file->getPathname());

			}

			$_ENV['secrets'] = $secrets;
			unset($secrets);

		}

		if(!$secrets_loaded){

			die('Secrets have not been loaded! You may need to set at least the encryption secret.');

		}

	}

}