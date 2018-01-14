<?php

namespace DevPledge\Controllers;


abstract class Base extends \DevPledge\Core\Base {
	public function __construct() {
		parent::__construct();
	}

	abstract public function render();
}