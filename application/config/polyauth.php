<?php

//PolyAuth Options

$config['polyauth'] = array(
	'email'				=> true,
	'session_handler'	=> new PolyAuth\Sessions\EncryptedSessionHandler($_ENV['secrets']['encryption_key']),
);