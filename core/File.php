<?php
/**
 * @author Martin Lindhe, 2007-2011 <martin@ubique.se>
 */

//STATUS: wip

namespace cd;

// normal file types defined in constants.php
define('FILETYPE_PROCESS',            50);
define('FILETYPE_CLONE_CONVERTED',    51);

class File
{
    var $id;
    var $type;          ///< in constants.php
    var $name;          ///< orginal filename
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
     * Calculates the upload directory for the specified file id
     * @return full local path to uploaded file
     */
    public static function getUploadPath($id)
    {
        $page = XmlDocumentHandler::getInstance();

        if (!$page->getUploadPath()) {
            throw new \Exception('No XmlDocumentHandler upload path configured!');
        }

        $dir_limit = 2000; // number of files per subdirectory

        $low_range = floor($id / $dir_limit) * $dir_limit;

        $dir = $page->getUploadPath().'/'.$low_range.'to'.($low_range+$dir_limit);

        if (!is_dir($dir)) {
            mkdir($dir);
            chmod($dir, 0777);
        }

        return $dir.'/'.$id;
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
        $list = Sql::pSelect($q, 'i', $type);

        return SqlObject::loadObjects($list, __CLASS__);
    }

    public static function getByCategory($type, $cat, $uploader = 0)
    {
        $q =
        'SELECT * FROM '.self::$tbl_name.
        ' WHERE type = ?'.
        ' AND category = ?'.
        ($uploader ? ' AND uploader = ?' : '').
        ' AND time_deleted IS NULL';

        if ($uploader) {
            $list = Sql::pSelect($q, 'iii', $type, $cat, $uploader);
        } else {
            $list = Sql::pSelect($q, 'ii', $type, $cat);
        }

        return SqlObject::loadObjects($list, __CLASS__);
    }

    public function store()
    {
        return SqlObject::store($this, self::$tbl_name, 'id');
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
        if (!file_exists($name)) {
            throw new \Exception('cant sync nonexisting file, what do???');
        }

        $size = filesize($name);
        $mime = get_mimetype_of_file($name);

        $q =
        'UPDATE tblFiles'.
        ' SET size = ?, mimetype = ?'.
        ' WHERE id = ?';
        Sql::pUpdate($q, 'isi', $size, $mime, $id);
    }

    public static function passthru($id) /// XXX enable this in a view instead, it dont belong here
    {
        $path = self::getUploadPath($id);

        $f = self::get($id);

        // Displays the file in the browser, and assigns a filename for the browser's "save as..." features
        header('Content-Disposition: inline; filename="'.basename($f->name).'"');
        header('Content-Transfer-Encoding: binary');

        $page = XmlDocumentHandler::getInstance();

        $page->disableDesign();
        $page->setMimeType($f->mimetype);

        if ($f->size) {
            header('Content-Length: '. $f->size);
        }

        readfile($path);
    }

    public static function importFromDisk($type, $filename, $category = 0)
    {
        if (!file_exists($filename)) {
            return false;
        }

        $key = array();
        $key['size'] = filesize($filename);
        $key['name'] = basename($filename);
        $key['tmp_name'] = $filename;
        $key['type'] = get_mimetype_of_file($filename);
        return self::import($type, $key, $category, true);
    }

    /**
     * @param $key array from a $_FILES entry
     * @param $blind dont verify if is_uploaded_file(), useful when importing files from other means than HTTP uploads
     * @return file id
     */
    public static function import($type, &$key, $category = 0, $blind = false)
    {
        // ignore empty file uploads
        if (!$key['name']) {
            return false;
        }

        if (!$blind && !is_uploaded_file($key['tmp_name'])) {
            throw new \Exception('Upload failed for file '.$key['name']);
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
        $file->time_uploaded = sql_datetime(time());
        $file->id = $file->store();
        if (!$file->id) {
            return false;
        }

        $dst_file = self::getUploadPath($file->id);

        if ($blind) {
            // UGLY HACK using "@": currently gives a E_WARNING: "Operation not permitted" error,
            // even though the rename suceeds???
            if (!(@rename($key['tmp_name'], $dst_file))) {
                throw new \Exception('rename failed');
            }

        } elseif (!move_uploaded_file($key['tmp_name'], $dst_file)) {
            throw new \Exception('Failed to move file from '.$key['tmp_name'].' to '.$dst_file);
        }

        chmod($dst_file, 0777);

        $key['name'] = $dst_file;
        $key['file_id'] = $file->id;

        return $file->id;
    }

    /**
     * Helper function that imports a image file and shrinks it to max allowed dimensions
     */
    public static function importImage($type, &$key, $category = 0, $blind = false, $max_width = 800, $max_height = 800)
    {
        $error = ErrorHandler::getInstance();

        if (!file_exists($key['tmp_name'])) {
            throw new \Exception('file '.$key['tmp_name'].' dont exist!');
        }

        $info = getimagesize($key['tmp_name']);
        switch ($info['mime']) {
        case 'image/jpeg': break;
        case 'image/png': break;
        case 'image/gif': break;
        default:
            $error->add('Uploaded file '.$key['name'].' is not an image (mimetype '.$info['mime'].')');
            return false;
        }

        $fileId = self::import($type, $key, $category, $blind);
        if (!$fileId) {
            return false;
        }

        $im = new ImageResizer(File::get($fileId));

        if ($im->width >= $max_width || $im->height >= $max_height) {
            $im->resizeAspect($max_width, $max_height);
            $im->render($im->mimetype, self::getUploadPath($fileId));
            self::sync($fileId); //updates tblFiles.size
        }

        return $fileId;
    }
}
