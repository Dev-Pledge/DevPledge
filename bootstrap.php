<?php

require 'vendor/autoload.php';

spl_autoload_register( function ( $name ) {
	$path         = explode( '\\', $name );
	$requiredPath = '../' . join( '/', $path ) . '.php';
	if ( file_exists( $requiredPath ) ) {
		require_once $requiredPath;
	}
} );

define( 'BASE', __DIR__ );
use Giraffe\Giraffe;
Giraffe::setEnvironment(ENVIRONMENT);
Giraffe::setProject('DevPledge');
Giraffe::setDeveloperEmails(
	'john@yettimedia.co.uk'
);
Giraffe::setJSDIR(__DIR__ . '/public/assets/js/giraffe/');
$app = new \DevPledge\App();

return $app->run(true);
