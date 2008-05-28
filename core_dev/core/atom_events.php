<?php
/**
 * $Id$
 *
 * Generic event logging functions
 *
 * \author Martin Lindhe, 2007-2008 <martin@startwars.org>
 */

define('EVENT_USER_LOGIN',			0x01);
define('EVENT_USER_LOGOUT',			0x02);

define('EVENT_CALL_BEGIN',			0x50);
define('EVENT_CALL_END',			0x51);
define('EVENT_RECORDED_PRES',		0x52);
define('EVENT_RECORDED_MSG',		0x53);
define('EVENT_RECORDED_CHATREQ',	0x54);
define('EVENT_PRES_APPROVED',		0x55);	//referer = fileId
define('EVENT_PRES_DENIED',			0x56);	//referer = fileId
define('EVENT_RECORDED_BLOG',		0x57);	//referer = fileId
define('EVENT_CALLER_UNREGISTER',	0x58);

//XXX: implement these:
define('EVENT_WARNED',				0x58);	//need to store warn reason

$event_name[EVENT_USER_LOGIN] = 'User login';
$event_name[EVENT_USER_LOGOUT] = 'User logout';
$event_name[EVENT_PRES_APPROVED] = 'Approved pres.';
$event_name[EVENT_PRES_DENIED] = 'Denied pres.';

$event_name[EVENT_CALL_BEGIN] = 'Call begin';
$event_name[EVENT_CALL_END] = 'Call end';
$event_name[EVENT_RECORDED_PRES] = 'Recorded pres.';
$event_name[EVENT_RECORDED_MSG] = 'Recorded msg.';
$event_name[EVENT_RECORDED_CHATREQ] = 'Recorded chatreq.';
$event_name[EVENT_RECORDED_BLOG] = 'Recorded blog';
$event_name[EVENT_CALLER_UNREGISTER] = 'Caller unregistered';

/**
 * Creates a new event
 */
function addEvent($_type, $_category = 0 , $ownerId = 0, $_referer = 0)
{
	global $db;
	if (!is_numeric($_type) || !is_numeric($_category) || !is_numeric($ownerId) || !is_numeric($_referer)) return false;

	$q = 'INSERT INTO tblEvents SET type='.$_type.', category='.$_category.', ownerId='.$ownerId.',refererId='.$_referer.',timeCreated=NOW()';
	return $db->insert($q);
}

/**
 * Returns objects in event log
 */
function getEvents($_category = 0, $ownerId = 0, $limit = '')
{
	global $db;
	if (!is_numeric($_category) || !is_numeric($ownerId)) return false;

	$q = 'SELECT * FROM tblEvents';
	if ($_category) {
		$q .= ' WHERE category='.$_category;
		if ($ownerId) $q .= ' AND ownerId='.$ownerId;
	}
	$q .= ' ORDER BY timeCreated DESC'.$limit;

	return $db->getArray($q);
}

/**
 * Returns number of objects in event log
 */
function getEventCnt($_category = 0, $ownerId = 0)
{
	global $db;
	if (!is_numeric($_category) || !is_numeric($ownerId)) return false;

	$q = 'SELECT COUNT(*) FROM tblEvents';
	if ($_category) {
		$q .= ' WHERE category='.$_category;
		if ($ownerId) $q .= ' AND ownerId='.$ownerId;
	}

	return $db->getOneItem($q);
}
?>
