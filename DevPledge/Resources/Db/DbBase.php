<?php

namespace Resources\Db;

use Resources\Base;
use Resources\BaseData;
use TomWright\Database\ExtendedPDO\ExtendedPDO;

/**
 * Class DbBase
 * @package Resources
 *
 */
abstract class DbBase extends Base {
	/**
	 * @var ExtendedPDO
	 */
	protected static $db;

	private $dbPrimaryId;

	private $dbTable;

	private $dbPrimaryColumn;

	protected $dbData;

	protected $data;

	/**
	 * DbBase constructor.
	 *
	 * @param $id
	 *
	 * @throws DbException
	 */
	public function __construct( $id = null ) {
		$this->getDataFromId( $id );
	}

	/**
	 * @return string
	 */
	public function getDbPrimaryColumn() {
		return $this->dbPrimaryColumn;
	}

	/**
	 * @param string $dbPrimaryColumn
	 *
	 * @return $this
	 */
	public function setDbPrimaryColumn( $dbPrimaryColumn ) {
		$this->dbPrimaryColumn = $dbPrimaryColumn;

		return $this;
	}

	/**
	 * @return Base
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @param string $data
	 *
	 * @return $this
	 */
	protected function setData( $data ) {
		$unserializedData = unserialize( $data );
		if ( $unserializedData instanceof BaseData ) {
			$this->data = $unserializedData;
		}

		return $this;
	}

	/**
	 * @return \stdClass | false
	 */
	public function getDbData() {
		return $this->dbData;
	}

	/**
	 * @param \stdClass | false $dbData
	 */
	public function setDbData( $dbData ) {
		if ( $dbData instanceof \stdClass ) {
			$this->dbData = $dbData;
			$this->doMapping();
			if ( $data = $this->getDataVar( 'data', false ) ) {
				$this->setData( $data );
			}
			if ( isset( $dbData->{$this->dbPrimaryColumn} ) ) {
				$this->dbPrimaryId = $dbData->{$this->dbPrimaryColumn};
			}
		}

	}

	/**
	 * @param null $id
	 *
	 * @return bool
	 * @throws DbException
	 */
	protected function getDataFromId( $id = null ) {

		if ( ! ( isset( $this->dbTable ) && isset( $this->dbPrimaryColumn ) ) ) {
			throw new DbException( 'DB Table and DB Primary Column needs to be set!' );
		}
		if ( ! isset( $id ) ) {
			return false;
		}
		$sql         = "SELECT * FROM `{$this->dbTable}` WHERE `{$this->dbPrimaryColumn}` = :id ;";
		$bind[':id'] = $id;
		$this->setDbData( $this->getDb()->queryRow( $sql, $bind ) );

		return $this->dbData;
	}

	/**
	 * @return ExtendedPDO
	 */
	protected function getDb() {
		return static::$db;
	}

	/**
	 * @param ExtendedPDO $db
	 */
	public static function setDb( ExtendedPDO $db ) {
		static::$db = $db;
	}

	/**
	 * @return int | bool
	 */
	public function getDbPrimaryId() {
		return isset( $this->dbPrimaryId ) ? $this->dbPrimaryId : false;
	}


	/**
	 * @param string $dbTable
	 *
	 * @return $this
	 */
	protected function setDbTable( $dbTable ) {
		$this->dbTable = $dbTable;

		return $this;
	}

	protected function getDataVar( $var, $default = null ) {
		$data = $this->getDbData();
		if ( isset( $data->{$var} ) ) {
			return $data->{$var};
		}

		return $default;
	}

	/**
	 * @return DbColumn[]
	 */
	abstract protected function getSaveColumns();

	/**
	 * @return bool
	 */
	abstract protected function canSave();

	/**
	 * @return bool
	 */
	public function save() {
		if ( ! $this->canSave() ) {
			return false;
		}
		$columns     = $this->getSaveColumns();
		$setSqlArray = array();
		if ( $columns ) {
			$bind = array();

			foreach ( $columns as $column ) {
				$bind[ $column->getBind() ] = $column->getValue();
				$setSqlArray[]              = '`' . $column->getColumn() . '`=' . $column->getBind();
			}

		}

		if ( $this->getData() instanceof BaseData ) {
			$bind[':data'] = serialize( $this->getData() );
			$setSqlArray[] = '`data`=:data';
		}

		$nowDateTime = new \DateTime();
		if ( $this->getDbPrimaryId() ) {
			$bind[':id']       = $this->getDbPrimaryId();
			$bind[':modified'] = $nowDateTime->format( 'Y-m-d H:i:s' );
			$setSqlArray[]     = '`modified`=:modified';
			$setSql            = 'SET ' . join( ', ', $setSqlArray );
			$sql               = 'UPDATE `' . $this->dbTable . '` ' . $setSql . ' WHERE `' . $this->dbPrimaryColumn . '`=:id ;';

			return $this->getDb()->dbQuery( $sql, $bind );
		} else {
			$bind[':created']  = $nowDateTime->format( 'Y-m-d H:i:s' );
			$bind[':modified'] = $nowDateTime->format( 'Y-m-d H:i:s' );
			$setSqlArray[]     = '`created`=:created';
			$setSqlArray[]     = '`modified`=:modified';
			$setSql            = 'SET ' . join( ', ', $setSqlArray );
			$sql               = 'INSERT INTO `' . $this->dbTable . '` ' . $setSql . ' WHERE `' . $this->dbPrimaryColumn . '`=:id ;';

			return $this->dbPrimaryId = $this->getDb()->dbQuery( $sql, $bind );
		}
	}

	/**
	 * @return bool
	 */
	public function isFound() {
		if ( isset( $this->dbPrimaryId ) ) {
			return true;
		}

		return false;
	}

}