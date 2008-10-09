<?php
/**
 * $Id$
 *
 * Object oriented interface for MySQL databases using the php_mysql.dll extension
 *
 * When possible, use class.DB_MySQLi.php instead (it is faster)
 *
 * \author Martin Lindhe, 2007-2008 <martin@startwars.org>
 */

require_once('class.DB_Base.php');

class DB_MySQL extends DB_Base
{
	/**
	 * Destructor
	 *
	 * \return nothing
	 */
	function __destruct()
	{
		if ($this->db_handle) mysql_close($this->db_handle);
	}

	/**
	 * Opens a connection to MySQL database
	 *
	 * \return nothing
	 */
	function connect()
	{
		global $config;

		if (!empty($config['debug'])) $time_started = microtime(true);

		//MySQL defaults
		if (!$this->host) $this->host = 'localhost';
		if (!$this->port) $this->port = 3306;	//MySQL default port
		if (!$this->username) $this->username = 'root';

		$this->db_handle = mysql_connect($this->host.':'.$this->port, $this->username, $this->password);

		if (!$this->db_handle) {
			$this->db_handle = false;
			if (!empty($config['debug'])) die('DB_MySQL: Database connection error '.mysql_errno().': '.mysql_error().'.</bad>');
			else die;
		}

		mysql_select_db($this->database, $this->db_handle);

		$this->query('SET NAMES '.$this->charset);

		$this->db_driver = 'DB_MySQL';
		$this->dialect = 'mysql';
		$this->server_version = mysql_get_server_info($this->db_handle);
		$this->client_version = mysql_get_client_info();

		if (!empty($config['debug'])) $this->profileConnect($time_started);
	}

	/**
	 * Shows MySQL driver status
	 *
	 * \return nothing
	 */
	function showDriverStatus()
	{
		echo 'Host info: '.mysql_get_host_info($this->db_handle).'<br/>';
		echo 'Connection character set: '.mysql_client_encoding($this->db_handle).'<br/>';
		echo 'Last error: '.mysql_error($this->db_handle).'<br/>';
		echo 'Last errno: '.mysql_errno($this->db_handle);
	}

	/**
	 * Escapes the string for use in MySQL queries
	 *
	 * \param $q the query to escape
	 * \return escaped query
	 */
	function escape($q)
	{
		return mysql_real_escape_string($q, $this->db_handle);
	}

	/**
	 * Executes a MySQL query
	 *
	 * \param $q the query to execute
	 * \return result
	 */
	function query($q)
	{
		global $config;

		if (!empty($config['debug'])) $time_started = microtime(true);

		$result = mysql_query($q, $this->db_handle);

		if (!$result) {
			if (!empty($config['debug'])) $this->query_error[ $this->queries_cnt ] = mysql_error($this->db_handle);
			else die; //if debug is turned off (production) and a query fail, just die silently
		}

		if (!empty($config['debug'])) $this->profileQuery($time_started, $q);

		return $result;
	}

	/**
	 * Helper function for MySQL INSERT queries
	 *
	 * \param $q the query to execute
	 * \return insert_id
	 */
	function insert($q)
	{
		global $config;

		if (!empty($config['debug'])) $time_started = microtime(true);

		$result = mysql_query($q, $this->db_handle);

		$ret_id = 0;

		if ($result) {
			$ret_id = mysql_insert_id($this->db_handle);
		} else {
			if (!empty($config['debug'])) $this->query_error[ $this->queries_cnt ] = mysql_error($this->db_handle);
			else die; //if debug is turned off (production) and a query fail, just die silently
		}

		if (!empty($config['debug'])) $this->profileQuery($time_started, $q);

		return $ret_id;
	}

	/**
	 * Helper function for MySQL DELETE queries
	 *
	 * \param $q the query to execute
	 * \return number of rows affected
	 */
	function delete($q)
	{
		global $config;

		if (!empty($config['debug'])) $time_started = microtime(true);

		$result = mysql_query($q, $this->db_handle);

		$affected_rows = false;

		if ($result) {
			$affected_rows = mysql_affected_rows($this->db_handle);
		} else {
			if (!empty($config['debug'])) $this->query_error[ $this->queries_cnt ] = mysql_error($this->db_handle);
			else die; //if debug is turned off (production) and a query fail, just die silently
		}

		if (!empty($config['debug'])) $this->profileQuery($time_started, $q);

		return $affected_rows;
	}

	/**
	 * Helper function for MySQL SELECT queries who returns array of data
	 *
	 * \param $q the query to execute
	 * \return result
	 */
	function getArray($q)
	{
		global $config;

		if (!empty($config['debug'])) $time_started = microtime(true);

		if (!$result = mysql_query($q, $this->db_handle)) {
			if (!empty($config['debug'])) $this->profileError($time_started, $q, mysql_error($this->db_handle));
			return array();
		}

		$data = array();

		while ($row = mysql_fetch_assoc($result)) {
			$data[] = $row;
		}

		mysql_free_result($result);

		if (!empty($config['debug'])) $this->profileQuery($time_started, $q);

		return $data;
	}

	/**
	 * Helper function for MySQL SELECT queries who returns mapped array of data
	 *
	 * \param $q the query to execute
	 * \return result
	 */
	function getMappedArray($q)
	{
		global $config;

		if (!empty($config['debug'])) $time_started = microtime(true);

		if (!$result = mysql_query($q, $this->db_handle)) {
			if (!empty($config['debug'])) $this->profileError($time_started, $q, mysql_error($this->db_handle));
			return array();
		}

		$data = array();

		while ($row = mysql_fetch_row($result)) {
			$data[ $row[0] ] = $row[1];
		}

		mysql_free_result($result);

		if (!empty($config['debug'])) $this->profileQuery($time_started, $q);

		return $data;
	}

	/**
	 * Helper function for MySQL SELECT queries who returns array of data with numerical index
	 *
	 * \param $q the query to execute
	 * \return result
	 */
	function getNumArray($q)
	{
		global $config;

		if (!empty($config['debug'])) $time_started = microtime(true);

		if (!$result = mysql_query($q, $this->db_handle)) {
			if (!empty($config['debug'])) $this->profileError($time_started, $q, mysql_error($this->db_handle));
			return array();
		}

		$data = array();

		while ($row = mysql_fetch_row($result)) {
			$data[] = $row[0];
		}

		mysql_free_result($result);

		if (!empty($config['debug'])) $this->profileQuery($time_started, $q);

		return $data;
	}

	/**
	 * Helper function for MySQL SELECT queries who returns one row of data
	 *
	 * \param $q the query to execute
	 * \return result
	 */
	function getOneRow($q)
	{
		global $config;

		if (!empty($config['debug'])) $time_started = microtime(true);

		if (!$result = mysql_query($q, $this->db_handle)) {
			if (!empty($config['debug'])) $this->profileError($time_started, $q, mysql_error($this->db_handle));
			return array();
		}

		if (mysql_num_rows($result) > 1) {
			die('ERROR: query '.$q.' in DB_MySQL::getOneRow() returned more than 1 result!');
		}

		$data = mysql_fetch_array($result, MYSQL_ASSOC);
		mysql_free_result($result);

		if (!empty($config['debug'])) $this->profileQuery($time_started, $q);

		return $data;
	}

	/**
	 * Helper function for MySQL SELECT queries who returns one entry of data
	 *
	 * \param $q the query to execute
	 * \return result
	 */
	function getOneItem($q)
	{
		global $config;

		if (!empty($config['debug'])) $time_started = microtime(true);

		if (!$result = mysql_query($q, $this->db_handle)) {
			if (!empty($config['debug'])) $this->profileError($time_started, $q, mysql_error($this->db_handle));
			return '';
		}

		if (mysql_num_rows($result) > 1) {
			die('ERROR: query '.$q.' in DB_MySQL::getOneItem() returned more than 1 result!');
		}

		$data = mysql_fetch_row($result);
		mysql_free_result($result);

		if (!empty($config['debug'])) $this->profileQuery($time_started, $q);

		if (!$data) return false;
		return $data[0];
	}

	/**
	 * Lock table from reading
	 */
	function lock($t)
	{
		$this->query('LOCK TABLES '.$t.' READ');
	}

	/**
	 * Unlock tables
	 */
	function unlock()
	{
		$this->query('UNLOCK TABLES');
	}

}
?>
