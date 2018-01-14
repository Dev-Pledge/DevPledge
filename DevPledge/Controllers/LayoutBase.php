<?php

namespace DevPledge\Controllers;

use DevPledge\View;

abstract class LayoutBase extends Base {
	/**
	 * @var View
	 */
	protected $view;
	/**
	 * @var string
	 */
	protected $viewContent = '';
	protected $viewHeader = '';

	public function __construct() {
		parent::__construct();
		$this->setView( new View() );
	}


	public function render() {
		echo ( new View( 'BaseLayout', [ 'content' => $this->getViewContent() ] ) )->getOutput();
	}

	/**
	 * @return View
	 *
	 */
	public function getView(): View {
		return $this->view;
	}

	/**
	 * @param View $view
	 *
	 * @return $this
	 */
	public function setView( View $view ) {
		$this->view = $view;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getViewContent(): string {
		return $this->viewContent;
	}

	/**
	 * @param string $view
	 * @param array $data
	 *
	 * @return $this
	 */
	public function addViewContent( string $view, $data = [] ) {
		$this->viewContent = $this->viewContent . $this->getView()->getView( $view, $data, true );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getViewHeader(): string {
		return $this->viewHeader;
	}

	/**
	 * @param string $view
	 * @param array $data
	 *
	 * @return $this
	 */
	public function addViewHeader( string $view, $data = [] ) {
		$this->viewHeader = $this->viewHeader . $this->getView()->getView( $view, $data, true );

		return $this;
	}

}