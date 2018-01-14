<?php

namespace DevPledge;

/**
 * Class View
 * @package DevPledge
 */
class View {

	protected static $output = '';

	/**
	 * View constructor.
	 *
	 * @param string|null $view
	 * @param array $data
	 * @param bool $return
	 */
	public function __construct( string $view = null, $data = array(), $return = false ) {
		if ( isset( $view ) && is_string( $view ) ) {
			$this->getView( $view, $data, $return );
		}
	}

	public function render() {
		echo $this->getOutput();
	}

	/**
	 * @return string
	 */
	public function getOutput(): string {
		return static::$output;
	}

	/**
	 * @param string $output
	 *
	 * @return string
	 */
	public function addOutput( string $output ) {
		static::$output = static::$output . $output;

		return $output;
	}

	/**
	 * @param string $view
	 * @param array $data
	 * @param bool $return
	 *
	 * @return string
	 */
	public function getView( string $view, $data = array(), $return = false ) {
		ob_start();
		extract( $data );
		include( BASE . '/DevPledge/Views/' . $view . '.php' );
		$view = ob_get_clean();
		if ( $return ) {
			return $view;
		}

		return $this->addOutput( $view );
	}

}