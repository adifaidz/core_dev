<?php
/**
 * $Id$
 *
 * Store user/server or other custom types of settings in database
 *
 * @author Martin Lindhe, 2007-2009 <martin@startwars.org>
 */

//STATUS: deprecated, use Setting.php

die('atom_settings.php deprecated!');


$config['settings']['default_signature'] = 'Signature';    //default name of the userdata field used to contain the forum signature



/**
 * Loads the owner of a name-value pair
 * This assumes a setting has unique value for each user (such as a custom activation code)
 *
 * @return userid owning the particular setting, or false
 */
function loadSettingOwner($_type, $categoryId, $settingName, $settingValue)
{
    global $db;
    if (!is_numeric($_type) || !is_numeric($categoryId)) return false;

    $q = 'SELECT ownerId FROM tblSettings';
    $q .= ' WHERE settingType='.$_type;
    $q .= ' AND categoryId='.$categoryId;
    $q .= ' AND settingName="'.$db->escape($settingName).'"';
    $q .= ' AND settingValue="'.$db->escape($settingValue).'"';
    return $db->getOneItem($q);
}

function loadSettingById($_type, $categoryId, $ownerId, $settingId, $all = false)
{
    global $db;

    if ($all) $q = 'SELECT * FROM tblSettings';
    else      $q = 'SELECT settingValue FROM tblSettings';
    $q .= ' WHERE settingType='.$_type;
    if ($categoryId) $q .= ' AND categoryId='.$categoryId;
    if ($ownerId) $q .= ' AND ownerId='.$ownerId;
    $q .= ' AND settingId='.$settingId;

    if ($all) return $db->getOneRow($q);
    return $db->getOneItem($q);
}

function readAllSettings($_type, $categoryId = 0, $ownerId = 0)
{
    //echo "readAllSettings() IS DEPRECATED!!";
    return loadSettings($_type, $categoryId, $ownerId);
}

/**
 * Returns array of all settings for requested owner
 *
 * @param $_type type of settings
 * @param $categoryId setting category (use 0 for all)
 * @param $ownerId owner of the settings
 * @return array of settings
 */
function loadSettings($_type, $categoryId = 0, $ownerId = 0)
{
    global $db;
    if (!is_numeric($_type) || !is_numeric($categoryId) || !is_numeric($ownerId)) return false;

    $q = 'SELECT * FROM tblSettings';
    $q .= ' WHERE settingType='.$_type;
    if ($categoryId) $q .= ' AND categoryId='.$categoryId;
    if ($ownerId) $q .= ' AND ownerId='.$ownerId;
    $q .= ' ORDER BY settingName ASC';
    return $db->getArray($q);
}

/**
 * Deletes all settings for owner, of specified type
 *
 * @param $_type type of settings
 * @param $categoryId setting category (use 0 for all)
 * @param $ownerId owner of the settings
 * @return number of settings removed
 */
function deleteSettings($_type, $categoryId, $ownerId)
{
    global $db;
    if (!is_numeric($_type) || !is_numeric($categoryId) || !is_numeric($ownerId)) return false;

    $q = 'DELETE FROM tblSettings WHERE ownerId='.$ownerId.' AND settingType='.$_type;
    if ($categoryId) $q .= ' AND categoryId='.$categoryId;
    return $db->delete($q);
}

/**
 * Deletes specified setting for owner, of specified type
 *
 * @param $_type type of setting
 * @param $categoryId setting category
 * @param $ownerId owner of the setting
 * @param $settingName name of the setting
 * @return number of settings removed
 */
function deleteSetting($_type, $categoryId, $ownerId, $settingName)
{
    global $db;
    if (!is_numeric($_type) || !is_numeric($categoryId) || !is_numeric($ownerId)) return false;

    $q = 'DELETE FROM tblSettings WHERE ownerId='.$ownerId;
    $q .= ' AND categoryId='.$categoryId;
    $q .= ' AND settingType='.$_type;
    $q .= ' AND settingName="'.$db->escape($settingName).'" LIMIT 1';
    return $db->delete($q);
}

function deleteSettingById($_type, $categoryId, $ownerId, $settingId)
{
    global $db;
    if (!is_numeric($_type) || !is_numeric($categoryId) || !is_numeric($ownerId) || !is_numeric($settingId)) return false;

    $q = 'DELETE FROM tblSettings WHERE ownerId='.$ownerId;
    $q .= ' AND categoryId='.$categoryId;
    $q .= ' AND settingType='.$_type;
    $q .= ' AND settingId='.$settingId;
    return $db->delete($q);
}

?>
