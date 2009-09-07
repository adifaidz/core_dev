<?php

//XXX separate sql to a sql_xxx base class

class category
{
	//tblCategory.categoryType: System categories. Reserved 1-50. Use a number above 50 for your own category types
	const USERFILE =  1; ///< normal, public userfile
	const WIKIFILE =  4; ///< category for wiki file attachments, to allow better organization if needed
	const TODOLIST =  5; ///< todo list categories

	const BLOG     = 10; ///< normal, personal blog category
	const CONTACT  = 11; ///< friend relation category, like "Old friends", "Family"
	const USERDATA = 12; ///< used for multi-choice userdata types. tblCategories.ownerId = tblUserdata.fieldId
	const POLL     = 13; ///< used for multi-choice polls. tblCategories.ownerId = tblPolls.pollId
	const LANGUAGE = 14; ///< represents a language, for multi-language features
	const NEWS     = 20; ///< news categories

	const GENERIC  = 30; ///< application specific categories


	//tblCategory.permissions:
	const PERM_PUBLIC  = 0x01; ///< public category
	const PERM_PRIVATE = 0x02; ///< owner and owner's friends can see the content
	const PERM_HIDDEN  = 0x04; ///< only owner can see the content

	const PERM_USER    = 0x40; ///< category is created by user
	const PERM_GLOBAL  = 0x80; ///< category is globally available to all users


	private $type;            ///< category type
	private $owner;           ///< owner id, the meaning depends on category type
	private $permissions = 0; ///<
	private $creator;         ///< if set, stores creatorId when categories are created
	private $tbl_name;        ///< table name

	function __construct($type)
	{
		if (!is_numeric($type)) return false;
		$this->type = $type;
		$this->tbl_name = 'tblCategories';
	}

	function setOwner($id)
	{
		if (!is_numeric($id)) return false;
		$this->owner = $id;
	}

	function setCreator($id)
	{
		if (!is_numeric($id)) return false;
		$this->creator = $id;
	}

	function setPermissions($flags)
	{
		if (!is_numeric($flags)) return false;
		$this->permissions = $flags;
	}

	/**
	 * Creates a new category, if it exists return id
	 *
	 * @return category id
	 * @return id of category $name
	 */
	function add($name)
	{
		global $db;

		$name = $db->escape(trim($name));
		if (!$name) return false;

		$q = 'SELECT categoryId FROM '.$this->tbl_name.' WHERE categoryType='.$this->type.' AND categoryName="'.$name.'"';
		if ($this->owner) $q .= ' AND ownerId='.$this->owner;
		if ($this->creator) $q .= ' AND creatorId='.$this->creator;
		$id = $db->getOneItem($q);
		if ($id) return $id;

		$q = 'INSERT INTO '.$this->tbl_name.' SET categoryType='.$this->type.',categoryName="'.$name.'"';
		$q .= ',timeCreated=NOW(),permissions='.$this->permissions;
		if ($this->owner) $q .= ',ownerId='.$this->owner;
		if ($this->creator) $q .= ',creatorId='.$this->creator;
		return $db->insert($q);
	}

	/**
	 * Returns a list of id->name pairs for the list
	 */
	function getList()
	{
		global $db;

		$q  = 'SELECT categoryId,categoryName FROM '.$this->tbl_name.' WHERE categoryType='.$this->type.' ';
		if ($this->owner) $q .= 'AND ownerId='.$this->owner.' ';
		$q .= 'ORDER BY categoryName ASC';

		return $db->getMappedArray($q);
	}


}

?>