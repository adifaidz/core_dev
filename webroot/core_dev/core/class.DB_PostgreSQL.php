<?
/*
	Object oriented interface for PostgreSQL databases using the ???.dll extension

	Written by Martin Lindhe, 2007
*/

require_once('class.DB_Base.php');

class DB_PostgreSQL extends DB_Base
{
	function __destruct()
	{
		if ($this->db_handle) pg_close($this->db_handle);
	}

	function showDriverStatus()
	{
		die('fixme-showDriverStatus()');
		/*
		echo 'Server info: '.$this->db_handle->server_info.' ('.$this->db_handle->host_info.')<br/>';
		echo 'Client info: '.$this->db_handle->client_info.'<br/>';
		echo 'Character set: '.$this->db_handle->character_set_name().'<br/>';
		echo 'Last error: '.pg_last_error($this->db_handle).'<br/>';
		echo 'Last errno: '.$this->db_handle->errno.'<br/><br/>';
		*/
	}

	function escape($q)
	{
		return pg_escape_string($this->db_handle, $q);
	}

	function connect()
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		//PostgreSQL defaults
		if (!$this->host) $this->host = 'localhost';
		if (!$this->port) $this->port = 5432;	//PostgreSQL default port
		if (!$this->username) $this->username = 'postgres';

		$str = 'host='.$this->host.' user='.$this->username.' password='.$this->password.' dbname='.$this->database.' port='.$this->port;
		$this->db_handle = pg_connect($str);

		if ($this->db_handle == false)
		{
			$this->db_handle = false;

			die('<bad>Database connection error.</bad>');
		}

		$this->db_driver = 'DB_PostgreSQL';
		$this->dialect = 'mysql';
		$this->server_version = 'unknown-fixme';//$this->db_handle->server_info;
		$this->client_version = 'unknown-fixme';//$this->db_handle->client_info;

		if ($config['debug']) $this->profileConnect($time_started);
	}

	function query($q)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		$result = pg_query($this->db_handle, $q);

		if (!$result) {
			if ($config['debug']) $this->query_error[ $this->queries_cnt ] = pg_last_error($this->db_handle);
			else die;	//if debug is turned off (production) and a query fail, just die silently
		}

		if ($config['debug']) $this->profileQuery($time_started, $q);

		return $result;
	}

	function insert($q)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		$result = $this->db_handle->query($q);

		$ret_id = 0;
die('x');
		if ($result) {
			$ret_id = $this->db_handle->insert_id;
		} else {
			if ($config['debug']) $this->query_error[ $this->queries_cnt ] = pg_last_error($this->db_handle);
			else die; //if debug is turned off (production) and a query fail, just die silently
		}

		if ($config['debug']) $this->profileQuery($time_started, $q);

		return $ret_id;
	}

	function delete($q)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		$result = $this->db_handle->query($q);
die('x');
		$affected_rows = false;

		if ($result) {
			$affected_rows = $this->db_handle->affected_rows;
		} else {
			if ($config['debug']) $this->query_error[ $this->queries_cnt ] = pg_last_error($this->db_handle);
			else die; //if debug is turned off (production) and a query fail, just die silently
		}

		if ($config['debug']) $this->profileQuery($time_started, $q);

		return $affected_rows;
	}

	function getArray($q)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		if (!$result = $this->db_handle->query($q)) {
			if ($config['debug']) $this->profileError($time_started, $q, pg_last_error($this->db_handle));
			return array();
		}
die('x');
		$data = array();

		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}

		$result->free();

		if ($config['debug']) $this->profileQuery($time_started, $q);

		return $data;
	}

	function getMappedArray($q)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		if (!$result = $this->db_handle->query($q)) {
			if ($config['debug']) $this->profileError($time_started, $q, pg_last_error($this->db_handle));
			return array();
		}

		$data = array();

		while ($row = $result->fetch_row()) {
			$data[ $row[0] ] = $row[1];
		}
die('x');
		$result->free();

		if ($config['debug']) $this->profileQuery($time_started, $q);

		return $data;
	}

	function getNumArray($q)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		if (!$result = $this->db_handle->query($q)) {
			if ($config['debug']) $this->profileError($time_started, $q, pg_last_error($this->db_handle));
			return array();
		}

		$data = array();

		while ($row = $result->fetch_row()) {
			$data[] = $row[0];
		}
die('x');
		$result->free();

		if ($config['debug']) $this->profileQuery($time_started, $q);

		return $data;
	}

	function getOneRow($q)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		if (!$result = pg_query($this->db_handle, $q)) {
			if ($config['debug']) $this->profileError($time_started, $q, pg_last_error($this->db_handle));
			return array();
		}

		if ($result->num_rows > 1) {
			die('ERROR: query '.$q.' in DB_PostgreSQL::getOneRow() returned more than 1 result!');
		}
		die('x');

		$data = $result->fetch_array(MYSQLI_ASSOC);
		$result->free();

		if ($config['debug']) $this->profileQuery($time_started, $q);

		return $data;
	}

	function getOneItem($q, $num = false)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		if (!$result = pg_query($this->db_handle, $q)) {
			if ($config['debug']) $this->profileError($time_started, $q, pg_last_error($this->db_handle));
			return '';
		}

		if (pg_num_rows($result) > 1) {
			die('ERROR: query '.$q.' in DB_PostgreSQL::getOneItem() returned more than 1 result!');
		}

		$data = pg_fetch_row($result);

		if ($config['debug']) $this->profileQuery($time_started, $q);

		if (!$data) {
			if ($num) return 0;
			return false;
		}
		return $data[0];
	}
}
?>