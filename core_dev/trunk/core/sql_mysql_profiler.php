<?php
/**
 * $Id$
 *
 * MySQL driver profiler class
 *
 * @author Martin Lindhe, 2007-2010 <martin@startwars.org>
 */

//STATUS: wip
//TODO: make it a general sql profiler (reuse for ms-sql)

require_once('sql_mysql.php');

class ProfiledSqlQuery
{
    var $query;
    var $error;
    var $time;
    var $prepared = false;
    var $format; //for prepared statements
    var $params; //for prepared statements
}

class DatabaseMysqlProfiler extends DatabaseMySQL implements IDB_SQL
{
    var $ts_initial    = 0;       ///< microtime for db instance
    var $measure_start = 0;       ///< time when profiling started
    var $time_connect  = 0;       ///< time it took to connect to db
    var $queries       = array(); ///< array of ProfiledSqlQuery (queries executed)

    function __construct()
    {
        $this->ts_initial = microtime(true);

        mysqli_report(MYSQLI_REPORT_ERROR); // fails on sql syntax errors (????)
        //mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_STRICT); // all errors on, but disable exceptions (strict)
    }

    function getErrorCount()
    {
        $cnt = 0;

        foreach ($this->queries as $q)
            if ($q->error)
                $cnt++;

        return $cnt;
    }

    /**
     * Saves time for profiling current action (connect, execute query, ...)
     */
    private function measureStart()
    {
        $this->measure_start = microtime(true);
    }

    /**
     * Calculates the time it took to connect to database
     */
    private function measureConnect()
    {
        $this->time_connect = microtime(true) - $this->measure_start;
    }

    /**
     * Calculates the time it took to execute a query
     */
    private function &measureQuery($q)
    {
        $prof = new ProfiledSqlQuery();
        $prof->query = $q;
        $prof->time = microtime(true) - $this->measure_start;

        $this->queries[] = $prof;
        return $prof;
    }

    /**
     * Shows the profiler view
     */
    public function renderProfiler()
    {
        $view = new ViewModel('views/sql_profiler.php');
        return $view->render();
    }

    function connect()
    {
        $this->measureStart();
        parent::connect();
        $this->measureConnect();
    }

    function insert($q)
    {
        $this->measureStart();
        $res = parent::insert($q);
        $prof = &$this->measureQuery($q);

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

    function delete($q)
    {
        $this->measureStart();
        $res = parent::delete($q);
        $prof = &$this->measureQuery($q);

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

    function getOneItem($q)
    {
        $this->measureStart();
        $res = parent::getOneItem($q);
        $prof = &$this->measureQuery($q);

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

    function getOneRow($q)
    {
        $this->measureStart();
        $res = parent::getOneRow($q);
        $prof = &$this->measureQuery($q);

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

    function getArray($q)
    {
        $this->measureStart();
        $res = parent::getArray($q);
        $prof = &$this->measureQuery($q);

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

    function getMappedArray($q) //XXX = get2dArray
    {
        $this->measureStart();
        $res = parent::getMappedArray($q);
        $prof = &$this->measureQuery($q);

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

    function get1dArray($q)
    {
        $this->measureStart();
        $res = parent::get1dArray($q);
        $prof = &$this->measureQuery($q);

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

    function pSelect()
    {
        $args = func_get_args();

        $this->measureStart();

        $res = call_user_func_array(array('parent', 'pSelect'), $args);  // HACK to pass dynamic variables to parent method

        $prof = &$this->measureQuery($args[0]);
        $prof->prepared = true;

        if (isset($args[1]))
            $prof->format = $args[1];

        if (isset($args[2])) {
            $params = array();
            for ($i = 2; $i < count($args); $i++)
                $prof->params[] = $args[$i];
        }

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

    function pSelectItem()
    {
        $args = func_get_args();

        $this->measureStart();

        $res = call_user_func_array(array('parent', 'pSelectItem'), $args);  // HACK to pass dynamic variables to parent method

        $prof = &$this->measureQuery($args[0]);
        $prof->prepared = true;

        if (isset($args[1]))
            $prof->format = $args[1];

        if (isset($args[2])) {
            $params = array();
            for ($i = 2; $i < count($args); $i++)
                $prof->params[] = $args[$i];
        }

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

    function pSelectMapped()
    {
        $args = func_get_args();

        $this->measureStart();

        $res = call_user_func_array(array('parent', 'pSelectMapped'), $args);  // HACK to pass dynamic variables to parent method

        $prof = &$this->measureQuery($args[0]);
        $prof->prepared = true;

        if (isset($args[1]))
            $prof->format = $args[1];

        if (isset($args[2])) {
            $params = array();
            for ($i = 2; $i < count($args); $i++)
                $prof->params[] = $args[$i];
        }

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

    function pSelect1d()
    {
        $args = func_get_args();

        $this->measureStart();

        $res = call_user_func_array(array('parent', 'pSelect1d'), $args);  // HACK to pass dynamic variables to parent method

        $prof = &$this->measureQuery($args[0]);
        $prof->prepared = true;

        if (isset($args[1]))
            $prof->format = $args[1];

        if (isset($args[2])) {
            $params = array();
            for ($i = 2; $i < count($args); $i++)
                $prof->params[] = $args[$i];
        }

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

    function pDelete()
    {
        $args = func_get_args();

        $this->measureStart();

        $res = call_user_func_array(array('parent', 'pDelete'), $args);  // HACK to pass dynamic variables to parent method

        $prof = &$this->measureQuery($args[0]);
        $prof->prepared = true;

        if (isset($args[1]))
            $prof->format = $args[1];

        if (isset($args[2])) {
            $params = array();
            for ($i = 2; $i < count($args); $i++)
                $prof->params[] = $args[$i];
        }

        if ($res === false)
            $prof->error = $this->db_handle->error;

        return $res;
    }

}

?>
