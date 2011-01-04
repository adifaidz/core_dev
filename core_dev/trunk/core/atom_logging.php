<?php
/**
 * $Id$
 *
 * Logging functions
 *
 * @author Martin Lindhe, 2008-2011 <martin@startwars.org>
 */

//STATUS: used by kost only, should be reworked to a class

define('LOGLEVEL_NOTICE',    1);
define('LOGLEVEL_WARNING',    2);
define('LOGLEVEL_ERROR',    3);
define('LOGLEVEL_ALL',        5);

/**
 * Writes a log entry to the database
 */
function logEntry($str, $entryLevel = LOGLEVEL_NOTICE, $userId = 0, $IP = 0)
{
    global $db;
    if (!is_numeric($entryLevel)) return false;

    $q = 'INSERT INTO tblLogs SET entryText="'.$db->escape($str).'",entryLevel='.$entryLevel.',timeCreated=NOW(),userId='.$userId.',userIP='.$IP;
    return $db->insert($q);
}

?>
