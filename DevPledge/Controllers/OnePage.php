<?php

namespace DevPledge\Controllers;


class OnePage extends LayoutBase {


	public function creditHelp() {
		$this->addViewContent( 'CreditHelp', [ 'content' => 'Consumer Lady' ] );
	}


}