<?
/*
	Object oriented interface for MySQL databases using the MySQLi extension

	Written by Martin Lindhe, 2007
*/

require_once('class.DB_Base.php');

class DB_MySQLi extends DB_Base
{
	/* Destructor */
	public function __destruct()
	{
		if ($this->db_handle) $this->db_handle->close();
	}

	/* Opens a database connection */
	protected function connect()
	{
		if ($this->debug) $time_started = microtime(true);

		$this->db_driver = 'DB_MySQLi';

		$this->db_handle = @ new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);

		if (mysqli_connect_errno()) {
			$this->db_handle = false;
			die('Database connection error.');
		}

		if ($this->debug) $this->profileConnect($time_started);
	}

	/* Escapes a string for use in queries */
	public function escape($query)
	{
		return $this->db_handle->real_escape_string($query);
	}

	/* Performs a query that don't return anything */
	public function query($query)
	{
		if ($this->debug) $time_started = microtime(true);

		$result = $this->db_handle->query($query);

		if ($result) {
			$this->insert_id = $this->db_handle->insert_id;
			$result->free();
		} else if ($this->debug && !$result) {
			$this->query_error[ $this->queries_cnt ] = $this->db_handle->error;
		} else {
			//if debug is turned off (production) and a query fail, just die silently
			die;
		}

		if ($this->debug) $this->profileQuery($time_started, $query);
	}

	/* Returns an array with the results, with columns as array indexes */
	public function getArray($query)
	{
		if ($this->debug) $time_started = microtime(true);

		if (!$result = $this->db_handle->query($query)) return array();

		$rows = $result->num_rows;

		$data = array();

		for ($i=0; $i<$rows; $i++) {
			$data[$i] = $result->fetch_array(MYSQLI_ASSOC);
		}

		$result->free();

		if ($this->debug) $this->profileQuery($time_started, $query);

		return $data;
	}

	/* Returns one row-result with columns as array indexes */
	public function getOneRow($query)
	{
		if ($this->debug) $time_started = microtime(true);	

		if (!$result = $this->db_handle->query($query)) return array();

		if ($result->num_rows > 1) {
			die('ERROR: query '.$query.' in DB_MySQLi::getOneRow() returned more than 1 result!');
		}

		$data = $result->fetch_array(MYSQLI_ASSOC);
		$result->free();

		if ($this->debug) $this->profileQuery($time_started, $query);

		return $data;
	}

	/* Returns one column-result only (SELECT a FROM t WHERE id=1), where id is distinct */
	public function getOneItem($query)
	{
		if ($this->debug) $time_started = microtime(true);	

		if (!$result = $this->db_handle->query($query)) return array();

		if ($result->num_rows > 1) {
			die('ERROR: query '.$query.' in DB_MySQLi::getOneItem() returned more than 1 result!');
		}

		$data = $result->fetch_row();
		$result->free();

		if ($this->debug) $this->profileQuery($time_started, $query);

		if (!$data) return false;
		return $data[0];
	}

}
?>