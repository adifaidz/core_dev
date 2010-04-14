<?php
/**
 * $Id$
 *
 * Implements various types of blocking to the services
 *
 * The blocking rules are stored as textual strings to allow for great flexibility.
 *
 * @author Martin Lindhe, 2007-2008 <martin@startwars.org>
 */

define('BLOCK_IP',        1);        //block by IP address. should be able to transparently support IPv6
define('BLOCK_ANR',        2);        //block by a-number
define('BLOCK_USERID',    3);        //block by userid

/**
 * Creates a new blocking rule
 *
 * @param $_type type of block rule
 * @param $rule the actual rule
 */
function addBlock($_type, $rule)
{
    global $h, $db;
    if (!is_numeric($_type) || !trim($rule)) return false;

    $q = 'SELECT COUNT(*) FROM tblBlocks WHERE type='.$_type.' AND rule="'.$db->escape($rule).'"';
    if ($db->getOneItem($q)) return false;

    $q = 'INSERT INTO tblBlocks SET type='.$_type.',rule="'.$db->escape($rule).'",timeCreated=NOW()'.($h->session ? ',createdBy='.$h->session->id : '');
    $db->insert($q);
}

/**
 * Removes a blocking rule
 *
 * @param $_type type of block rule
 * @param $rule the actual rule
 */
 function removeBlock($_type, $rule)
{
    global $db;
    if (!is_numeric($_type) || !trim($rule)) return false;

    $q = 'DELETE FROM tblBlocks WHERE type='.$_type.' AND rule="'.$db->escape($rule).'"';
    return $db->delete($q);
}

/**
 * Removes a blocking rule
 *
 * @param $_type type of block rule
 * @param $rule the actual rule
 */
 function removeBlockId($_type, $ruleId)
{
    global $db;
    if (!is_numeric($_type) || !is_numeric($ruleId)) return false;

    $q = 'DELETE FROM tblBlocks WHERE type='.$_type.' AND ruleId='.$ruleId;
    return $db->delete($q);
}

/**
 * Returns an array of all blocks of specified type
 *
 * @param $_type type of block rule
 */
function getBlocks($_type, $_limit = '')
{
    global $db;
    if (!is_numeric($_type)) return false;

    $q = 'SELECT * FROM tblBlocks WHERE type='.$_type;
    if (!empty($limit)) {
        $q .= $_limit;
    }
    return $db->getArray($q);
}

/**
 * Returns count of all blocks of specified type
 *
 * @param $_type type of block rule
 */
function getBlocksCount($_type)
{
    global $db;
    if (!is_numeric($_type)) return false;

    $q = 'SELECT count(ruleId) FROM tblBlocks WHERE type='.$_type;
    return $db->getOneItem($q);
}

/**
 * Returns true if the specified blocking rule exists
 *
 * @param $_type type of block rule
 * @param $rule the rule to match with
 */
function isBlocked($_type, $rule)
{
    global $db;
    if (!is_numeric($_type)) return false;

    $q = 'SELECT COUNT(*) FROM tblBlocks WHERE type='.$_type.' AND rule="'.$db->escape($rule).'"';
    if ($db->getOneItem($q)) return true;
    return false;
}
?>
