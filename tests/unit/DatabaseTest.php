<?php

/**
 * @small
 */
class DatabaseTest extends PHPUnit_Framework_TestCase
{
	protected $dbConfig;
	protected $db;

	/**
	 * Test setUp
	 */
	public function setUp()
	{
		$this->dbConfig = array(
			'driver'	=> 'Pdo',
			'dsn'		=> 'mysql:host=localhost;dbname=eve',
			'username'	=> 'user',
			'password'	=> 'pass',
			'noerrors'	=> false,
		);

		$this->db = new Eve\Database();
	}

	/**
	 * @covers Eve\Database::getConnections
	 */
	public function testNoConnectionsAreInitiallySet()
	{
		$this->assertEmpty($this->db->getConnections());
	}

	/**
	 * @covers Eve\Database::setConnection
	 */
	public function testSetConnection()
	{
		extract($this->dbConfig);
		$conn = new \Eve\Database\Pdo($dsn, $username, $password, $noerrors);

		$this->assertInstanceOf('\Eve\Database\Pdo', $conn);
		$this->assertInstanceOf('\Eve\Database', $this->db->setConnection('db1', $conn));
		$this->assertNotEmpty($this->db->getConnections());
		$this->assertInstanceOf('\Eve\Database\Pdo', $this->db->getConnection('db1'));

		return $this->db;
	}

	/**
	 * @covers Eve\Database::__set
	 */
	public function testMagicSetConnection()
	{
		extract($this->dbConfig);
		$conn = new \Eve\Database\Pdo($dsn, $username, $password, $noerrors);

		$this->assertInstanceOf('\Eve\Database\Pdo', $conn);
		$this->db->db1 = $conn;
		$this->assertNotEmpty($this->db->getConnections());
		$this->assertInstanceOf('\Eve\Database\Pdo', $this->db->db1);
	}

	/**
	 * @covers Eve\Database::createConnection
	 */
	public function testValidCreateConnection()
	{
		$this->assertInstanceOf('\Eve\Database', $this->db->createConnection('db1', $this->dbConfig));
		$this->assertNotEmpty($this->db->getConnections());
		$this->assertInstanceOf('\Eve\Database\Pdo', $this->db->getConnection('db1'));
	}

	/**
	 * @covers Eve\Database::createConnection
	 */
	public function testInvalidCreateConnection()
	{
		try {
			$this->db->createConnection('db1', array());
			$this->fail('Did not throw exception');
		} catch (\Eve\Database\Exception $e) {
			$this->assertInstanceOf('\ErrorException', $e);
		}
	}

	/**
	 * @covers Eve\Database::getConnection
	 */
	public function testInvalidGetConnection()
	{
		try {
			$this->db->getConnection('nonexist');
			$this->fail('Did not throw exception');
		} catch (\Eve\Database\Exception $e) {
			$this->assertInstanceOf('\ErrorException', $e);
		}
	}

	/**
	 * @covers Eve\Database::getConnection
	 * @depends testSetConnection
	 */
	public function testValidGetConnection(\Eve\Database $db)
	{
		try {
			$conn = $db->getConnection('db1');
			$this->assertInstanceOf('\Eve\Database\Pdo', $conn);
		} catch (\Eve\Database\Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	/**
	 * @covers Eve\Database::__get
	 * @depends testSetConnection
	 */
	public function testInvalidMagicGetConnection(\Eve\Database $db)
	{
		try {
			$db->nonexist;
			$this->fail('Did not throw exception');
		} catch (\Eve\Database\Exception $e) {
			$this->assertInstanceOf('\ErrorException', $e);
		}
	}

	/**
	 * @covers Eve\Database::__get
	 * @depends testSetConnection
	 */
	public function testValidMagicGetConnection(\Eve\Database $db)
	{
		try {
			$conn = $db->db1;
			$this->assertInstanceOf('\Eve\Database\Pdo', $conn);
		} catch (\Eve\Database\Exception $e) {
			$this->fail($e->getMessage());
		}
	}
}
