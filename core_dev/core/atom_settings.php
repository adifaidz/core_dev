<?
/**
 * $Id$
 *
 * Store user/server or other custom types of settings in database
 *
 * \author Martin Lindhe, 2007-2008 <martin@startwars.org>
 */

	define('SETTING_USERDATA',			2);			//settings used to store personal userdata
	define('SETTING_CALLERDATA',		3);			//settings used to store data of a caller


	//TODO remove these and make more userdata field types instead
	$config['settings']['default_signature'] = 'Signature';	//default name of the userdata field used to contain the forum signature

	/**
	 * Saves a setting associated with $ownerId
	 *
	 * \param $_type type of setting
	 * \param $ownerId owner of the setting
	 * \param $settingName name of the setting, text-string
	 * \param $settingValue value of the setting
	 * \return true on success
	 */
	function saveSetting($_type, $ownerId, $settingName, $settingValue)
	{
		global $db;
		if (!is_numeric($ownerId) || !$ownerId || !is_numeric($_type) || !$settingName) return false;

		$settingName = $db->escape($settingName);
		$settingValue = $db->escape($settingValue);

		$q = 'SELECT settingId FROM tblSettings WHERE ownerId='.$ownerId.' AND settingType='.$_type.' AND settingName="'.$settingName.'"';
		if ($db->getOneItem($q)) {
			$q = 'UPDATE tblSettings SET settingValue="'.$settingValue.'",timeSaved=NOW() WHERE ownerId='.$ownerId.' AND settingType='.$_type.' AND settingName="'.$settingName.'"';
			$db->query($q);
		} else {
			$q = 'INSERT INTO tblSettings SET ownerId='.$ownerId.',settingType='.$_type.',settingName="'.$settingName.'",settingValue="'.$settingValue.'",timeSaved=NOW()';
			$db->insert($q);
		}

		return true;
	}

	/**
	 * Loads a setting associated with $ownerId
	 *
	 * \param $_type type of setting
	 * \param $ownerId owner of the setting
	 * \param $settingName name of the setting, text-string
	 * \param $defaultValue is the default value to return if no such setting was previously stored
	 * \return the value of the requested setting
	 */
	function loadSetting($_type, $ownerId, $settingName, $defaultValue = '')
	{
		global $db;
		if (!is_numeric($ownerId) || !$ownerId || !is_numeric($_type) || !$settingName) return false;

		$settingName = $db->escape($settingName);
		$defaultValue = $db->escape($defaultValue);

		$q = 'SELECT settingValue FROM tblSettings WHERE ownerId='.$ownerId.' AND settingType='.$_type.' AND settingName="'.$settingName.'"';
		$result = $db->getOneItem($q);

		if ($result) return $result;
		return $defaultValue;
	}

	/**
	 * Returns array of all settings for requested owner
	 *
	 * \param $_type type of settings
	 * \param $ownerId owner of the settings
	 * \return array of settings
	 */
	function readAllSettings($_type, $ownerId)
	{
		global $db;
		if (!is_numeric($ownerId) || !$ownerId || !is_numeric($_type)) return false;

		$q = 'SELECT settingName,settingId,settingValue FROM tblSettings WHERE ownerId='.$ownerId.' AND settingType='.$_type.' ORDER BY settingName ASC';
		return $db->getArray($q);
	}

	/**
	 * Deletes all settings for owner, of specified type
	 *
	 * \param $_type type of settings
	 * \param $ownerId owner of the settings
	 * \return number of settings removed
	 */
	function deleteSettings($_type, $ownerId)
	{
		global $db;
		if (!is_numeric($_type) || !is_numeric($ownerId)) return false;

		$q = 'DELETE FROM tblSettings WHERE ownerId='.$ownerId.' AND settingType='.$_type;
		return $db->delete($q);
	}

	/**
	 * Deletes specified setting for owner, of specified type
	 *
	 * \param $_type type of setting
	 * \param $ownerId owner of the setting
	 * \param $settingName name of the setting
	 * \return number of settings removed
	 */
	function deleteSetting($_type, $ownerId, $settingName)
	{
		global $db;
		if (!is_numeric($_type) || !is_numeric($ownerId)) return false;

		$q = 'DELETE FROM tblSettings WHERE ownerId='.$ownerId.' AND settingType='.$_type.' AND settingName = "'.$settingName.'" LIMIT 1';
		return $db->delete($q);
	}

	/**
	 * Deletes all settings for owner
	 *
	 * \param $_type type of settings
	 * \param $ownerId owner of the settings
	 * \return number of settings removed
	 */
	function deleteAllSettings($ownerId)
	{
		global $db;
		if (!is_numeric($ownerId)) return false;

		$q = 'DELETE FROM tblSettings WHERE ownerId='.$ownerId;
		return $db->delete($q);
	}

	/**
	 * Removes all occurances of settings with the specified paramets 
	 *
	 * \param $_type type of settings
	 * \param $settingName name of the setting
	 * \param $settingValue value of the setting
	 * \return number of settings removed
  	 */
	function deleteSettingByValue($_type, $settingName, $settingValue)
	{
		global $db;
		if (!is_numeric($_type)) return false;

		$q = 'DELETE FROM tblSettings WHERE type='.$_type.' AND settingName="'.$db->escape($settingName).'" AND settingValue="'.$db->escape($settingValue).'"';
		return $db->delete($q);
	}
?>
