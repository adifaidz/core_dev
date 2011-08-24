<?php
/**
 * $Id$
 *
 * A bookmark owned by a user, which points to a resource of some kind, such as another user (friend list)
 *
 * @author Martin Lindhe, 2011 <martin@startwars.org>
 */

//STATUS: wip

//TODO 2: easy methdo for "remove bookmark for object"

// use these, or any numbers >= 100 for application specific needs
define('BOOKMARK_CUSTOM',  100);
define('BOOKMARK_CUSTOM2', 101);
define('BOOKMARK_CUSTOM3', 102);

class Bookmark
{
    var $id;
    var $owner;
    var $type;
    var $value;

    protected static $tbl_name = 'tblBookmarks';

    static function get($id)
    {
        return SqlObject::getById($id, self::$tbl_name, __CLASS__);
    }

    static function getList($owner, $type)
    {
        if (!is_numeric($owner) || !is_numeric($type))
            throw new Exception ('noo');

        $q =
        'SELECT * FROM '.self::$tbl_name.
        ' WHERE owner = '.$owner.
        ' AND type = '.$type;

        return SqlObject::loadObjects($q, __CLASS__); // XXX pselect?
    }

    /**
     * Check if an object (owner id & type) is already bookmarked
     */
    static function exists($owner, $type)
    {
        $o = new Bookmark();
        $o->value = $owner;
        $o->type = $type;

        return SqlObject::exists($o, self::$tbl_name);
    }

    static function store($obj)
    {
        return SqlObject::storeUnique($obj, self::$tbl_name);
    }

    /**
     * Creates a new bookmark
     * @param $type
     * @param $bookmark_id
     * @param $owner  if not set, owner will be current user
     */
    static function create($type, $bookmark_id, $owner = 0)
    {
        $session = SessionHandler::getInstance();

        $o = new Bookmark();
        $o->type = $type;
        $o->value = $bookmark_id;
        $o->owner = $owner ? $owner : $session->id;
        self::store($o);
    }

}

?>
