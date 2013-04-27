<?php

    //assume autoloading is setup
     
    //ok so this file is in Guzzle/Url directory
    namespace Guzzle\Url;
     
    //we're importing something we want to use
    use Guzzle\Cookie\CookieParser;
     
    //here we're importing, but we're aliasing it too!
    use Guzzle\Message as Awesome;
     
    //here we're importing a directory, just imagine that it is a directory and not a file
    use Guzzle\Directory;
     
    class Mapper{
     
        public function __construct(){
     
            //there's never any need to specify filename extensions, the classes and files are the same name.
     
            //this works, because we imported the file (remember that class names should match file names)
            //it first looks at the current namespace, then the imported namespace
            //this is called an unqualified name
            $cookie_parser = new CookieParser; 
     
            //this may work, it's not imported, so the current namespace gets appended
            //it will only work if there was a Guzzle/Url/UrlParser.php
            //this is also called an unqualified name
            $url_parser = new UrlParser;
     
            //this is called a qualified name, the current namespace gets appended to it
            //it becomes Guzzle\Url\Another\Parser.php
            $another_parser = new Another\Parser;
     
            //this is called a FULLY qualified name, it does not append any of the current namespaces, and it does not recognise imported namespaces. It's like an absolute path.
            $absolute_parser = new \Other\Cool\Parser;
     
            //this uses an alias, and resolves to Guzzle\Message\Parsing.php
            $aliased_parser = new Awesome\Parsing;
     
            //this is not an alias, instead it points to the Directory import. So it resolves as Guzzle\Directory\SuperParser;
            $directory_parser = new Directory\SuperParser;
            //therefore if the use keyword points to a directory and not to a specific file, all of those files become fair game. However you need to use the last namespace directory and prefix all class initialisations
			
			$cookie_parser->what();
     
        }
     
    }

