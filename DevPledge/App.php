<?php

namespace DevPledge;

use FastRoute\RouteCollector;
use TomWright\Database\ExtendedPDO\ExtendedPDO;

class App {
	/**
	 * @var Controllers\DisplayBase
	 */
	protected $controller;

	public function run( $displayErrors = false ) {

		if ( $displayErrors ) {
			ini_set( 'display_errors', 1 );
			ini_set( 'display_startup_errors', 1 );
			error_reporting( E_ALL );
		}
		$this->initDb();

		$dispatcher = \FastRoute\simpleDispatcher( function ( RouteCollector $r ) {
			include( 'Routes.php' );
		} );
		// Fetch method and URI from somewhere
		$httpMethod = $_SERVER['REQUEST_METHOD'];
		$uri        = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
		if ( false !== $pos = strpos( $uri, '?' ) ) {
			$uri = substr( $uri, 0, $pos );
		}
		$uri = rawurldecode( $uri );

		$routeInfo = $dispatcher->dispatch( $httpMethod, $uri );
		switch ( $routeInfo[0] ) {
			case \FastRoute\Dispatcher::NOT_FOUND:
				echo 'sorry not found';
				break;
			case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				$allowedMethods = $routeInfo[1];
				// ... 405 Method Not Allowed
				echo 'sorry not allowed!';
				break;
			case \FastRoute\Dispatcher::FOUND:
				$handler = $routeInfo[1];
				$vars    = $routeInfo[2];
				// ... call $handler with $vars
				$split  = explode( '@', $handler );
				$class  = $split[0];
				$method = $split[1];
				$class  = '\\DevPledge\\Controllers\\' . $class;
				$this->setController( new $class );

				if ( is_callable( array( $this->getController(), $method ) ) ) {
					call_user_func_array( array( $this->getController(), $method ), $vars );
				}
				if ( is_callable( array( $this->getController(), 'render' ) ) ) {
					call_user_func_array( array( $this->getController(), 'render' ), array() );
				}
				break;
		}
	}

	/**
	 * @return Controllers\DisplayBase
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * @param Controllers\Base $controller
	 */
	public function setController( Controllers\Base $controller ) {
		$this->controller = $controller;
	}

	protected function initDb() {
		if ( file_exists( BASE . '/JsonSetup/db.json' ) ) {
			$dbSetupJson = file_get_contents( BASE . '/JsonSetup/db.json' );
			$dbSetup     = json_decode( $dbSetupJson );
			if ( is_object( $dbSetup ) &&
			     isset( $dbSetup->dsn ) &&
			     isset( $dbSetup->username ) &&
			     isset( $dbSetup->password ) ) {
				ExtendedPDO::createConnection(
					$dbSetup->dsn,
					$dbSetup->username,
					$dbSetup->password,
					'devpledge'
				);
			}
		}

	}
}