<?php
/**
 * Created by PhpStorm.
 * User: johnsaunders
 * Date: 06/01/2018
 * Time: 17:11
 */

namespace Resources\Db;

/**
 * Class DbColumn
 * @package Resources\Db
 */
class DbColumn {

	protected $column;
	protected $value;
	protected $uniqueId;

	/**
	 * DbColumn constructor.
	 *
	 * @param $column
	 * @param null $value
	 */
	public function __construct( $column, $value = null ) {
		$this->setColumn( $column )->setValue( $value );
		$this->uniqueId = uniqid( '__' );
	}

	public function getBind() {
		return ':' . $this->column . $this->uniqueId;
	}

	/**
	 * @return string
	 */
	public function getColumn() {
		return $this->column;
	}

	/**
	 * @param string $column
	 *
	 * @return $this
	 */
	public function setColumn( $column ) {
		$this->column = $column;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return ( isset( $this->value ) ) ? $this->value : '';
	}

	/**
	 * @param $value
	 *
	 * @return $this
	 */
	public function setValue( $value = null ) {
		$this->value = $value;

		return $this;
	}
}