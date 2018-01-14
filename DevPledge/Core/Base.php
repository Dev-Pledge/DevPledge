<?php

namespace DevPledge\Core;


use TomWright\Database\ExtendedPDO\ExtendedPDO;

/**
 * Class Base
 * @package DevPledge\Core
 */
abstract class Base {
	public function __construct() {

	}

	/**
	 * @return ExtendedPDO
	 */
	public function getDb() {
		return ExtendedPDO::getInstance( 'devpledge' );
	}
}