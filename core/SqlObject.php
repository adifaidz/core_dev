<?php
/**
 * $Id$
 *
 * Reads database columns into properties of objects
 *
 * @author Martin Lindhe, 2011-2014 <martin@ubique.se>
 */

//STATUS: wip

//XXX move some of the methods to Sql.php

namespace cd;

require_once('core.php');
require_once('Sql.php');

class ReflectedObject
{
    var $str;
    var $props = array();  ///< array of ReflectedProperty
    var $cols  = array();  ///< class properties / table column names
    var $vals  = array();  ///< class property / table column value
}

class SqlObject
{
    /**
     * Creates one object $classname
     * @param $q a sql select query resulting in one row
     * @param $classname name of class object to load rows into
     */
    public static function loadObject($q, $classname)
    {
        if (!$q) {
            throw new \Exception ("unexpected empty input");

            // return new $classname();   // TODO-LATER default to always return a object of type $classname
            return false;
        }

        if (is_array($q)) {

            d($q);
            throw new \Exception ("array input to loadObject no longer supported, use RowToObject");
        }

        throw new \Exception ("FIXME use Sql::pSelectRowToObject() instead");
    }

    /**
     * Creates an array of $objname objects from input query/indexed array
     * @param $q       a sql select query resulting in multiple rows, or a array of rows
     * @param $classname name of class object to load rows into
     */
    public static function loadObjects($q, $classname)
    {
        if (is_array($q)) {
            d($q);
            throw new \Exception ("input array no longer supported, use ListToObjects");
        }

        throw new \Exception ("XXXX FIFXM");
    }

    public static function ListToObjects($list, $classname)
    {
        $res = array();
        foreach ($list as $row)
            $res[] = self::RowToObject($row, $classname);

        return $res;
    }

    public static function RowToObject($row, $classname)
    {
        if (!is_array($row))
            throw new \Exception ('RowToObject fail, need array, got: '.$row);

        if (class_exists(__NAMESPACE__.'\\'.$classname))
            $classname = __NAMESPACE__.'\\'.$classname;

        $reflect = new \ReflectionClass($classname);
        $props   = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

        $found = array();
        $obj = new $classname();
        foreach ($props as $prop)
        {
            $n = $prop->getName();

            if (array_key_exists($n, $row)) {
                $obj->$n = $row[ $n ];
                $found[$n] = true;
                continue;
            }

            throw new \Exception ('class '.$classname.' expects database column "'.$n.'" which dont exist');
        }

        if ( count($props) != count($row))
        {
            foreach ($row as $idx => $col) {
                if (!array_key_exists($idx, $found))
                    throw new \Exception ('class '.$classname.' misses define of variable "'.$idx.'" which is found in table');
            }

            throw new \Exception ('class '.$classname.' ERROR - SHOULD NOT HAPPEN!');
        }

        return $obj;
    }

    /**
     * Helper function, useful for parsing an object for a XhtmlForm dropdown etc
     * @return id->name paired array
     */
    public static function getListAsIdNameArray($arr, $id_name = 'id', $name_name = 'name')   // XXXX rename?
    {
        $res = array();
        foreach ($arr as $o)
            $res[ $o->$id_name ] = $o->$name_name;

        return $res;
    }

    /**
     * Creates part of a sql statement out of public properties of $obj
     *
     * @param $obj
     * @param $exclude_col
     * @param $include_unset  shall unset object properties be included in result?
     * @return ReflectedObject
     */
    protected static function reflectQuery($obj, $exclude_col = '', $include_unset = true)
    {
        $reflect = new \ReflectionClass($obj);
        $props   = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

        // auto escape column names for reserved SQL words
        // full list at http://dev.mysql.com/doc/refman/5.5/en/reserved-words.html
        // the list is huge, so we only cover common use cases
        $reserved_words = array('desc', 'default', 'group', 'from', 'to', 'value');

        $res = new ReflectedObject();

        foreach ($props as $prop)
        {
            $col = $prop->getName();
            if ($col == $exclude_col)
                continue;

            if (!$include_unset && !$obj->$col)
                continue;

            $res->str .= self::stringForm($obj->$col);

            $res->vals[] = $obj->$col;

            if (in_array($col, $reserved_words))
                $res->cols[] = '`'.$col.'` = ?';
            else
                $res->cols[] = $col.' = ?';
        }
        //d($res);
        return $res;
    }

    public static function stringForm($s)
    {
        if ($s && !is_string($s) && !is_numeric($s)) {
            d($s);
            throw new \Exception ('not a string');
        }

        if (substr($s, 0, 1) == '0')
            return 's';

        if (is_numeric($s) && strpos($s, '.') !== false)
            return 'd';

        if (numbers_only($s))
            return 'i';
        return 's';
    }

    public static function idExists($id, $tblname, $field_name = 'id')
    {
        if (!is_alphanumeric($tblname) || !is_alphanumeric($field_name))
            throw new \Exception ('very bad');

        $q =
        'SELECT COUNT(*) FROM '.$tblname.
        ' WHERE '.$field_name.' = ?';

        $form = self::stringForm($id);

        return Sql::pSelectItem($q, $form, $id) ? true : false;
    }

    /**
     * Compares the object:s set properties to table columns
     * @return true if object exists
     **/
    public static function exists($obj, $tblname)
    {
        if (!is_alphanumeric($tblname))
            throw new \Exception ('very bad');

        $reflect = self::reflectQuery($obj, '', false);

        $q =
        'SELECT COUNT(*) FROM '.$tblname.
        ' WHERE '.implode(' AND ', $reflect->cols);

        return Sql::pSelectItem($q, $reflect->str, $reflect->vals) ? true : false;
    }

    /**
     * Creates a object in a database table
     * @return insert id
     */
    public static function create($obj, $tblname)
    {
        if (!is_alphanumeric($tblname))
            throw new \Exception ('very bad');

        $reflect = self::reflectQuery($obj, '', false);

        if (!$reflect->cols)
            throw new \Exception ('no columns defined for '.$tblname);

        $q = 'INSERT INTO '.$tblname.
        ' SET '.implode(', ', $reflect->cols);

        return Sql::pInsert($q, $reflect->str, $reflect->vals);
    }

    /**
     * If object exists with same name as field in $field_name, already in db, return false
     */
    public static function storeUnique($obj, $tblname)
    {
        if (self::exists($obj, $tblname))
            return false;

        return self::create($obj, $tblname);
    }

    /**
     * @param $field_name if object exists with this name, then update that item
     */
    public static function store($obj, $tblname, $field_name = 'id')
    {
        if ($obj->$field_name && SqlObject::idExists($obj->$field_name, $tblname, $field_name))
        {
//            throw new \Exception ('obj fieldname: '.$obj->$field_name.' tbl '.$tblname);

            SqlObject::updateId($obj, $tblname, $field_name);
            return $obj->$field_name;
        }

        return SqlObject::create($obj, $tblname);
    }

    public static function getById($id, $tblname, $classname, $field_name = 'id')
    {
        return self::getByField($id, $tblname, $classname, $field_name);
    }

    public static function getByField($val, $tblname, $classname, $field_name)
    {
        if (!is_alphanumeric($tblname))
            throw new \Exception ('tblname should be alphanumeric, isnt: '.$tblname);
        if (!is_alphanumeric($field_name))
            throw new \Exception ('field_name should be alphanumeric, isnt: '.$field_name);

        $form = self::stringForm($val);

        $q =
         'SELECT * FROM '.$tblname.
        ' WHERE '.$field_name.' = ?';

        return Sql::pSelectRowToObject($classname, array($q, $form, $val));
    }

    public static function deleteById($id, $tblname, $field_name = 'id')
    {
        if (!is_alphanumeric($tblname) || !is_alphanumeric($field_name))
            throw new \Exception ('very bad');

        if (!is_numeric($id))
            throw new \Exception ('bad data'. $id);

        $q =
         'DELETE FROM '.$tblname.
        ' WHERE '.$field_name.' = ?';
        Sql::pDelete($q, 'i', $id);
    }

    /**
     * Fetches all items
     * @param $tblname
     * @param $classname
     */
    public static function getAll($tblname, $classname, $order_field = '', $order = 'asc')
    {
        if (!is_alphanumeric($tblname) || !is_alphanumeric($order_field)) {
            throw new \Exception('very bad');
        }

        if (!Sql::isValidOrder($order)) {
            throw new \Exception('odd order '.$order);
        }

        $q =
        'SELECT * FROM '.$tblname.
        ($order_field ? ' ORDER BY '.$order_field.' '.strtoupper($order) : '');

        return Sql::pSelectToObjectList($classname, $q);
    }

    /**
     * Fetches all items where $field_name = $value
     * @param $field_name
     * @param $value
     * @param $tblname
     * @param $classname
     * @param $order_field
     * @param $order 'desc', 'asc' or empty
     */
    public static function getAllByField($field_name, $value, $tblname, $classname, $order_field = '', $order = 'asc')
    {
        if (!is_alphanumeric($tblname))
            throw new \Exception ('tblname should be alphanumeric, isnt: '.$tblname);
        if (!is_alphanumeric($field_name))
            throw new \Exception ('field_name should be alphanumeric, isnt: '.$field_name);

        if (!is_alphanumeric($order_field))
            throw new \Exception ('order_field should be alphanumeric, isnt: '.$order_field);

        if (!Sql::isValidOrder($order))
            throw new \Exception ('odd order '.$order);

        $form = self::stringForm($value);

        $q =
        'SELECT * FROM '.$tblname.' WHERE '.$field_name.' = ?'.
        ($order_field ? ' ORDER BY '.$order_field.' '.strtoupper($order) : '');

        $list = Sql::pSelect($q, $form, $value);

        return SqlObject::ListToObjects($list, $classname);
    }

    public static function updateId($obj, $tblname, $field_name = 'id')
    {
        if (!is_alphanumeric($tblname) || !is_alphanumeric($field_name))
            throw new \Exception ('very bad');

        if (!$obj->$field_name)
        {
            d($obj);
            throw new \Exception ('eehh');
        }

        $reflect = self::reflectQuery($obj, $field_name);

        $q =
        'UPDATE '.$tblname.
        ' SET '.implode(', ', $reflect->cols).
        ' WHERE '.$field_name.' = ?';

        $reflect->str .= (is_numeric($obj->$field_name) ? 'i' : 's');
        $reflect->vals[] = $obj->$field_name;

        return Sql::pUpdate($q, $reflect->str, $reflect->vals);
    }

}
