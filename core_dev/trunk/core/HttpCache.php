<?php
/**
 * $Id$
 *
 * @author Martin Lindhe, 2012 <martin@startwars.org>
 */

namespace cd;

class HttpCache
{
    var $id;
    var $url;
    var $time_saved;
    var $raw;

    protected static $tbl_name = 'tblHttpCache';

    public static function get($id)
    {
        return SqlObject::getById($id, self::$tbl_name, __CLASS__);
    }

    public static function store($obj)
    {
        return SqlObject::store($obj, self::$tbl_name, 'id');
    }

}

?>
