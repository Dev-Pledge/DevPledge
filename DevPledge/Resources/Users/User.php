<?php

namespace Resources\Users;

use Resources\Db\DbColumn;

/**
 * Class User
 * @package Resources\Users
 */
class User extends DbBase {


	protected $firstName;
	protected $lastName;
	protected $dob;
	protected $email;
	private $password;

	/**
	 * User constructor.
	 *
	 * @param null $id
	 * @param null $email
	 * @param null $password
	 *
	 * @throws \Resources\Db\DbException
	 */
	public function __construct( $id = null, $email = null, $password = null ) {
		$this->setDbTable( 'users' )
		     ->setDbPrimaryColumn( 'user_id' );
		if ( isset( $email ) && isset( $password ) ) {
			$this->getUserByEmailPassword( $email, $password );
		} else {
			parent::__construct( $id );
		}
	}

	/**
	 * @return string
	 */
	protected function getPassword() {
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword( $password ) {
		$this->password = $this->encryptPassword( $password );
	}

	protected function mapPassword() {
		$this->password = $this->getDataVar( 'password' );
	}

	private function encryptPassword( $password ) {
		return md5( $password );
	}

	/**
	 * @return string
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * @param $lastName
	 *
	 * @return $this
	 */
	public function setLastName( $lastName ) {
		$this->lastName = $lastName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * @param $firstName
	 *
	 * @return $this
	 */
	public function setFirstName( $firstName ) {
		$this->firstName = $firstName;

		return $this;
	}

	protected function mapFirstName() {
		$this->firstName = $this->getDataVar( 'first_name' );

		return $this;
	}

	/**
	 * @param bool $dateTime
	 *
	 * @return \DateTime | string | bool
	 */
	public function getDob( $dateTime = false ) {
		if ( $dateTime && isset( $this->dob ) ) {
			return new \DateTime( $this->dob );
		}

		return $this->dob;
	}

	/**
	 * @param $dob
	 *
	 * @return $this
	 */
	public function setDob( $dob ) {
		$this->dob = $dob;

		return $this;
	}

	protected function mapDob() {
		$this->dob = $this->getDataVar( 'dob' );
	}


	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param $email
	 *
	 * @return $this
	 */
	public function setEmail( $email ) {
		$this->email = strtolower( $email );

		return $this;
	}

	protected function mapEmail() {
		$this->email = $this->getDataVar( 'email' );
	}

	/**
	 * @return DbColumn[]
	 */
	protected function getSaveColumns() {
		return array(
			new DbColumn( 'first_name', $this->getFirstName() ),
			new DbColumn( 'last_name', $this->getLastName() ),
			new DbColumn( 'email', $this->getEmail() ),
			new DbColumn( 'dob', $this->getDob() )
		);
	}

	/**
	 * @return bool
	 */
	protected function canSave() {
		$checkArray = array( 'firstName', 'lastName', 'password', 'email' );

		foreach ( $checkArray as $checkKey ) {
			if ( ! ( isset( $this->{$checkKey} ) && ! empty( $this->{$checkKey} ) ) ) {
				return false;
			}
			if ( $checkKey == 'email' && strpos( $this->{$checkKey}, '@' ) === false ) {
				return false;
			}
		}

		return true;
	}

	protected function getUserByEmailPassword( $email, $password ) {
		$password = $this->encryptPassword( $password );
		$sql      = "SELECT * FROM users WHERE email = :email AND password = :password ;";
		$bind     = array(
			':email'    => strtolower( $email ),
			':password' => $password
		);
		$this->setDbData( $this->getDb()->queryRow( $sql, $bind ) );

		return $this->isFound();
	}
}