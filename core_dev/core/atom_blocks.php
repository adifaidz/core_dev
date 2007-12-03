<?
/**
 * atom_blocks.php - Implements various types of blocking to the services
 *
 * The blocking rules are stored as textual strings to allow for great flexibility.
 *
 * \author Martin Lindhe, 2007
 */

	define('BLOCK_IP',		1);		//block by IP address. should be able to transparently support IPv6
	define('BLOCK_ANR',		2);		//block by a-number

	/**
	 * Creates a new blocking rule
	 * \param $_type type of block rule
	 * \param $rule the actual rule
	 */
	function addBlock($_type, $rule)
	{
		global $db, $session;
		if (!is_numeric($_type) || !trim($rule)) return false;

		$q = 'SELECT COUNT(*) FROM tblBlocks WHERE type='.$_type.' AND rule="'.$db->escape($rule).'"';
		if ($db->getOneItem($q)) return false;

		if ($session) {
			$q = 'INSERT INTO tblBlocks SET type='.$_type.',rule="'.$db->escape($rule).'",timeCreated=NOW(),createdBy='.$session->id;
		} else {
			$q = 'INSERT INTO tblBlocks SET type='.$_type.',rule="'.$db->escape($rule).'",timeCreated=NOW()';
		}
		$db->insert($q);
	}

	/**
	 * Removes a blocking rule
	 * \param $_type type of block rule
	 * \param $rule the actual rule
	 */
	function removeBlock($_type, $rule)
	{
		global $db;
		if (!is_numeric($_type) || !trim($rule)) return false;

		$q = 'DELETE FROM tblBlocks WHERE type='.$_type.' AND rule="'.$db->escape($rule).'"';
		return $db->delete($q);
	}

	/**
	 * Returns a array of all blocks of specified type
	 * \param $_type type of block rule
	 * \param $rule the actual rule
	 */
	function getBlocks($_type)
	{
		global $db;
		if (!is_numeric($_type)) return false;

		$q = 'SELECT * FROM tblBlocks WHERE type='.$_type;
		return $db->getArray($q);
	}

	/**
	 * Returns true if the specified blocking rule exists
	 * \param $_type type of block rule
	 * \param $rule the rule to match with
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