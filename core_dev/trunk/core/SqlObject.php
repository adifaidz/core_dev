<?php
/**
 * $Id$
 *
 * Reads database columns into properties of objects
 *
 * @author Martin Lindhe, 2011 <martin@startwars.org>
 */

//STATUS: wip ... XXX merge with Sql.php ?

require_once('Sql.php');

class SqlObject
{
    /**
     * Creates one object $objname
     * @param $q       a sql select query resulting in one row, or a indexed array
     * @param $classname name of class object to load rows into
     */
    static function loadObject($q, $classname)
    {
        $db = SqlHandler::getInstance();

        if (!$q) {
            return false;
//            return new $classname();
//            throw new Exception ('no query');
        }

        $row = is_array($q) ? $q : $db->pSelect($q);

        if (!is_array($row))
            throw new Exception ('loadObject fail, need array of rows, got: '.$row);

        $reflect = new ReflectionClass($classname);
        $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        $obj = new $classname();
        foreach ($props as $prop)
        {
            $n = $prop->getName();

            if (!array_key_exists($n, $row)) {
                d( $row);
                throw new Exception ('loadObject fail, db column named "'.$n.'" dont exist');
            }
            $obj->$n = $row[ $n ];
        }

        if ( count($props) != count($row))
        {
/*
            d($row);
            d( count($row ) );

            d($props);
            d( count($props ) );
*/
            throw new Exception ('loadObject fail, class '.$classname.' misses defined variables, or something!');
        }

        return $obj;
    }

    /**
     * Creates an array of $objname objects from input query/indexed array
     * @param $q       a sql select query resulting in multiple rows, or a array of rows
     * @param $classname name of class object to load rows into
     */
    static function loadObjects($q, $classname)
    {
        $list = is_array($q) ? $q : Sql::pSelect($q);

        $res = array();
        foreach ($list as $row)
            $res[] = self::loadObject($row, $classname);

        return $res;
    }

    /**
     * Creates part of a sql statement out of public properties of $obj
     *
     * @param $obj
     * @param $exclude_col
     * @param $include_unset  shall unset object properties be included in result?
     * @return array with key=val strings usable for sql queries
     */
    protected static function reflectQuery($obj, $exclude_col = '', $include_unset = true)
    {
        $reflect = new ReflectionClass($obj);
        $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        // full list at http://dev.mysql.com/doc/refman/5.5/en/reserved-words.html
        // the list is huge, so we only try to cover common ones
        $reserved_words = array('desc', 'default');

        $vals = array();
        foreach ($props as $prop)
        {
            $col = $prop->getName();
            if ($col == $exclude_col)
                continue;

            if (!$include_unset && !$obj->$col)
                continue;

            if (is_numeric($obj->$col))
                $val = $obj->$col;
            else
                $val = '"'.Sql::escape($obj->$col).'"';

            if (in_array($col, $reserved_words))
                // escape column names for reserved SQL words
                $col = '`'.$col.'`';

            $vals[] = $col.'='.$val;
        }

        return $vals;
    }

    static function idExists($id, $tblname, $field_name = 'id')
    {
        if (!is_alphanumeric($tblname) || !is_alphanumeric($field_name))
            throw new Exception ('very bad');

        $q =
        'SELECT COUNT(*) FROM '.$tblname.
        ' WHERE '.$field_name.' = ?';

        return Sql::pSelectItem($q, 's', $id) ? true : false;
    }

    /**
     * Compares the object:s set properties to table columns
     * @return true if object exists
     **/
    static function exists($obj, $tblname)
    {
        if (!is_alphanumeric($tblname))
            throw new Exception ('very bad');

        $vals = self::reflectQuery($obj, '', false);

        $q =
        'SELECT COUNT(*) FROM '.$tblname.
        ' WHERE '.implode(' AND ', $vals);

        return Sql::pSelectItem($q) ? true : false;  /// XXX use prepared select properly.. how?
    }

    /**
     * Creates a object in a database table
     * @return insert id
     */
    static function create($obj, $tblname)
    {
        if (!is_alphanumeric($tblname))
            throw new Exception ('very bad');

        $vals = self::reflectQuery($obj);

        $q =
        'INSERT INTO '.$tblname.
        ' SET '.implode(', ', $vals);
        return SqlHandler::getInstance()->insert($q); ///XXXXXXXXXXXX use Sql::pInsert ..!!!
    }

    /**
     * If object exists with same name as field in $field_name, already in db, return false
     */
    static function storeUnique($obj, $tblname)
    {
        if (self::exists($obj, $tblname))
            return false;

        return self::create($obj, $tblname);
    }

    /**
     * If object exists with the same name as field in $field_name, update that item
     */
    static function store($obj, $tblname, $field_name = 'id')
    {
        if (SqlObject::idExists($obj->$field_name, $tblname, $field_name)) {
            sqlObject::updateId($obj, $tblname, $field_name);
            return $obj->id;
        }

        return SqlObject::create($obj, $tblname);
    }

    static function getById($id, $tblname, $classname, $field_name = 'id')
    {
        return self::getByField($id, $tblname, $classname, $field_name);
    }

    static function getByField($val, $tblname, $classname, $field_name = 'id')
    {
        if (!is_alphanumeric($tblname) || !is_alphanumeric($field_name))
            throw new Exception ('very bad');

        if (is_numeric($val))
            $format = 'i';
        else
            $format = 's';

        $q =
         'SELECT * FROM '.$tblname.
        ' WHERE '.$field_name.' = ?';
        $row = Sql::pSelectRow($q, $format, $val);

        return SqlObject::loadObject($row, $classname);
    }

    static function deleteById($id, $tblname, $field_name = 'id')
    {
        if (!is_alphanumeric($tblname) || !is_alphanumeric($field_name))
            throw new Exception ('very bad');

        $q =
         'DELETE FROM '.$tblname.
        ' WHERE '.$field_name.' = ?';
        Sql::pDelete($q, 'i', $id);
    }

    /**
     * Fetches all items where $field_name = $value
     * @param $order 'desc', 'asc' or empty
     */
    static function getAllByField($field_name, $value, $tblname, $classname, $order_field = '', $order = 'asc')
    {
        if (!is_alphanumeric($tblname) || !is_alphanumeric($field_name) || !is_alphanumeric($order_field))
            throw new Exception ('very bad');

        if (!Sql::isValidOrder($order))
            throw new Exception ('odd order '.$order);

        $q = 'SELECT * FROM '.$tblname.' WHERE '.$field_name.' = ?';

        if (is_numeric($value))
            $form = 'i';
        else
            $form = 's';

        if ($order_field)
            $q .= ' ORDER BY '.$order_field.' '.strtoupper($order);

        $list = Sql::pSelect($q, $form, $value);

        return SqlObject::loadObjects($list, $classname);
    }

    static function updateId($obj, $tblname, $field_name = 'id')
    {
        if (!is_alphanumeric($tblname) || !is_alphanumeric($field_name))
            throw new Exception ('very bad');

        if (!$obj->id)
        {
            d($obj);
            throw new Exception ('eehh');
        }

        $vals = self::reflectQuery($obj, $field_name);

        $q =
        'UPDATE '.$tblname.
        ' SET '.implode(', ', $vals).
        ' WHERE '.$field_name.' = '.$obj->id;

        return SqlHandler::getInstance()->update($q);  //XXXXXXXXXXx use Sql::pUpdate
    }

}

?>
