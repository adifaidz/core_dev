<?
/*
	Object oriented interface for MySQL databases using the php_mysql.dll extension

	When possible, use class.DB_MySQLi.php instead (it is faster)

	Written by Martin Lindhe, 2007
*/

require_once('class.DB_Base.php');

class DB_MySQL extends DB_Base
{
	function __destruct()
	{
		if ($this->db_handle) mysql_close($this->db_handle);
	}
	
	function showDriverStatus()
	{
		echo 'Server info: '.mysql_get_server_info($this->db_handle).' ('.mysql_get_host_info($this->db_handle).')<br/>';
		echo 'Client info: '.mysql_get_client_info().'<br/>';
		echo 'Character set: '.mysql_client_encoding($this->db_handle).'<br/>';
		echo 'Last error: '.mysql_error($this->db_handle).'<br/>';
		echo 'Last errno: '.mysql_errno($this->db_handle).'<br/><br/>';
	}

	function escape($query)
	{
		return mysql_real_escape_string($query, $this->db_handle);
	}

	protected function connect()
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		$this->db_handle = @ mysql_connect($this->host.':'.$this->port, $this->username, $this->password);

		if (mysqli_connect_errno()) {
			$this->db_handle = false;
			die('Database connection error.');
		}

		mysql_select_db($this->database, $this->db_handle);

		$this->db_driver = 'DB_MySQL';
		$this->dialect = 'mysql';
		$this->server_version = mysql_get_server_info($this->db_handle);
		$this->client_version = mysql_get_client_info();

		if ($config['debug']) $this->profileConnect($time_started);
	}

	function query($query)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		$result = mysql_query($query, $this->db_handle);

		if ($result) {
			$this->insert_id = mysql_insert_id($this->db_handle);
		} else if ($config['debug'] && !$result) {
			$this->query_error[ $this->queries_cnt ] = mysql_error($this->db_handle);
		} else {
			//if debug is turned off (production) and a query fail, just die silently
			die;
		}

		if ($config['debug']) $this->profileQuery($time_started, $query);

		return $result;
	}

	function getArray($query)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		if (!$result = mysql_query($query, $this->db_handle)) {
			if ($config['debug']) $this->profileError($time_started, $query, mysql_error($this->db_handle));
			return array();
		}

		$data = array();

		while ($row = mysql_fetch_assoc($result)) {
			$data[] = $row;
		}

		mysql_free_result($result);

		if ($config['debug']) $this->profileQuery($time_started, $query);

		return $data;
	}
	
	function getMappedArray($query)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		if (!$result = mysql_query($query, $this->db_handle)) {
			if ($config['debug']) $this->profileError($time_started, $query, mysql_error($this->db_handle));
			return array();
		}

		$data = array();

		while ($row = mysql_fetch_row($result)) {
			$data[ $row[0] ] = $row[1];
		}

		mysql_free_result($result);

		if ($config['debug']) $this->profileQuery($time_started, $query);

		return $data;
	}

	function getNumArray($query)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);

		if (!$result = mysql_query($query, $this->db_handle)) {
			if ($config['debug']) $this->profileError($time_started, $query, mysql_error($this->db_handle));
			return array();
		}

		$data = array();

		while ($row = mysql_fetch_row($result)) {
			$data[] = $row[0];
		}

		mysql_free_result($result);

		if ($config['debug']) $this->profileQuery($time_started, $query);

		return $data;
	}
	
	function getOneRow($query)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);	

		if (!$result = mysql_query($query, $this->db_handle)) {
			if ($config['debug']) $this->profileError($time_started, $query, mysql_error($this->db_handle));
			return array();
		}

		if (mysql_num_rows($result) > 1) {
			die('ERROR: query '.$query.' in DB_MySQL::getOneRow() returned more than 1 result!');
		}

		$data = mysql_fetch_array($result, MYSQL_ASSOC);
		mysql_free_result($result);

		if ($config['debug']) $this->profileQuery($time_started, $query);

		return $data;
	}

	function getOneItem($query)
	{
		global $config;

		if ($config['debug']) $time_started = microtime(true);	

		if (!$result = mysql_query($query, $this->db_handle)) {
			if ($config['debug']) $this->profileError($time_started, $query, mysql_error($this->db_handle));
			return '';
		}

		if (mysql_num_rows($result) > 1) {
			die('ERROR: query '.$query.' in DB_MySQL::getOneItem() returned more than 1 result!');
		}

		$data = mysql_fetch_row($result);
		mysql_free_result($result);

		if ($config['debug']) $this->profileQuery($time_started, $query);

		if (!$data) return false;
		return $data[0];
	}

}
?>