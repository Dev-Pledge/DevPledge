<?php


namespace Resources;

/**
 * Class Base
 * @package Resources
 */
abstract class Base {

	protected function doMapping() {

		$methods = get_class_methods( $this );

		foreach ( $methods as $method ) {
			if ( substr( $method, 0, 3 ) === "map" ) {
				call_user_func_array( array( $this, $method ), array() );
			}
		}

	}

}