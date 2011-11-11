<?php
/**
 * $Id$
 *
 * @author Martin Lindhe, 2007-2011 <martin@startwars.org>
 */

//STATUS: wip

require_once('SqlObject.php');

define('FILETYPE_PROCESS',            50);
define('FILETYPE_CLONE_CONVERTED',    51);

class File
{
    var $id;
    var $type;
    var $name;
    var $size;
    var $mimetype;
    var $owner;
    var $category;
    var $uploader;
    var $uploader_ip;
    var $time_uploaded;
    var $time_deleted;

    protected static $tbl_name = 'tblFiles';

    /**
     * @return full local path to uploaded file
     */
    public static function getUploadPath($id)
    {
        $page = XmlDocumentHandler::getInstance();

        if (!$page->getUploadRoot())
            throw new Exception ('No upload root configured!');

        return $page->getUploadRoot().'/'.$id;
    }

    public static function get($id)
    {
        return SqlObject::getById($id, self::$tbl_name, __CLASS__, 'id');
    }

    public static function getList()
    {
        $q =
        'SELECT * FROM '.self::$tbl_name.
        ' ORDER BY time_uploaded ASC';
        return SqlObject::loadObjects($q, __CLASS__);
    }

    public static function getByType($type)
    {
        $q =
        'SELECT * FROM '.self::$tbl_name.
        ' WHERE type = ?'.
        ' AND time_deleted IS NULL';
        $list = SqlHandler::getInstance()->pSelect($q, 'i', $type);

        return SqlObject::loadObjects($list, __CLASS__);
    }

    public static function getByCategory($type, $cat)
    {
        $q =
        'SELECT * FROM '.self::$tbl_name.
        ' WHERE type = ?'.
        ' AND category = ?'.
        ' AND time_deleted IS NULL';
        $list = SqlHandler::getInstance()->pSelect($q, 'ii', $type, $cat);

        return SqlObject::loadObjects($list, __CLASS__);
    }

    public static function store($obj)
    {
        return SqlObject::store($obj, self::$tbl_name, 'id');
    }

    /** marks the file as deleted */
    public static function delete($id)
    {
        $q =
        'UPDATE tblFiles'.
        ' SET time_deleted = NOW()'.
        ' WHERE id = ?';
        Sql::pUpdate($q, 'i', $id);
    }

    /** permanently deletes the file from disk */
    public static function unlink($id)
    {
        SqlObject::deleteById($id, self::$tbl_name, 'id');
        $path = self::getUploadPath($id);
        unlink($path);
    }

    /** Updates tblFiles entry with current file size & mime type, useful after Image resize / rotate etc */
    public static function sync($id)
    {
        $name = self::getUploadPath($id);
        if (!file_exists($name))
            throw new Exception ('cant sync nonexisting file, what do???');

        $size = filesize($name);
        $mime = get_mimetype_of_file($name);

        $q =
        'UPDATE tblFiles'.
        ' SET size = ?, mimetype = ?'.
        ' WHERE id = ?';
        Sql::pUpdate($q, 'isi', $size, $mime, $id);
    }


    public static function passthru($id)
    {
        $path = self::getUploadPath($id);

        $f = self::get($id);

        // Displays the file in the browser, and assigns a filename for the browser's "save as..." features
        header('Content-Disposition: inline; filename="'.basename($f->name).'"');
        header('Content-Transfer-Encoding: binary');

        $page = XmlDocumentHandler::getInstance();

        $page->disableDesign();
        $page->setMimeType( $f->mimetype );

        if ($f->size)
            header('Content-Length: '. $f->size);

        readfile($path);
    }

    /**
     * @param $key array from $_FILES entry
     * @return file id
     */
    public static function import($type, &$key, $category = 0)
    {
        // ignore empty file uploads
        if (!$key['name'])
            return;

        if (!is_uploaded_file($key['tmp_name'])) {
            throw new Exception ('Upload failed for file '.$key['name'] );
            //$error->add('Upload failed for file '.$key['name'] );
            //return;
        }

        $session = SessionHandler::getInstance();

        $file = new File();
        $file->type = $type;
        $file->uploader = $session->id;
        $file->uploader_ip = client_ip();
        $file->size = $key['size'];
        $file->name = $key['name'];
        $file->mimetype = $key['type'];
        $file->category = $category;
        $file->time_uploaded = sql_datetime( time() );
        $id = self::store($file);

        $dst_file = self::getUploadPath($id);

        if (!move_uploaded_file($key['tmp_name'], $dst_file))
            throw new Exception ('Failed to move file from '.$key['tmp_name'].' to '.$dst_file);

        chmod($dst_file, 0777);

        $key['name'] = $dst_file;
        $key['file_id'] = $id;

        return $id;
    }

}

?>
