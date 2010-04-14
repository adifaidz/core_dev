<?php
/**
 * $Id$
 *
 * Files class - Handle file upload, image manipulating, file management
 *
 * Uses tblFiles
 * Uses php_id3.dll if enabled, to show more details of mp3s in the file module
 *
 * @author Martin Lindhe, 2007-2009 <martin@startwars.org>
 */

//STATUS: drop this code when rewrite is finished in class.Files.php, drop files module from session handler

//TODO: rename tblFiles.timeUploaded to tblFiles.timeCreated
//FIXME: use bmp mimetype "image/x-ms-bmp" and verify it is recognized in all browsers

//TODO: move image manipulation functions to a separate class

require_once('input_metainfo.php');
require_once('atom_comments.php');        //for image comments support
require_once('atom_categories.php');    //for file categories support
require_once('atom_subscriptions.php');    //for userfile area subscriptions
require_once('functions_image.php');

define('FILETYPE_WIKI',                1); // The file is a wiki attachment
define('FILETYPE_BLOG',                2); // The file is a blog attachment
define('FILETYPE_NEWS',                3); // The file is a news attachment
define('FILETYPE_FILEAREA_UPLOAD',     4); // File is uploaded to a file area
define('FILETYPE_USERFILE',            5); // File is uploaded to the user's own file area
define('FILETYPE_USERDATA',            6); // File is uploaded to a userdata field
define('FILETYPE_FORUM',               7); // File is attached to a forum post
define('FILETYPE_PROCESS',             8); // File uploaded to be processed

define('FILETYPE_VIDEOBLOG',          10); // video clip representing a user submitted blog
define('FILETYPE_VIDEOPRES',          11); // video clip representing a presentation of the user
define('FILETYPE_VIDEOMESSAGE',       12); // video clip respresenting a private message
define('FILETYPE_VIDEOCHATREQUEST',   13); // video clip representing a live videochat request
define('FILETYPE_VIDEOABORTED',       14); // video clip aborted during recording

define('FILETYPE_GENERIC',            20); // generic file type, for application specific file type

define('FILETYPE_CLONE_CONVERTED',    30); //converted from orginal file format (image/video/audio/document)
define('FILETYPE_CLONE_VIDEOTHUMB10', 31); //video thumbnail of video 10% into the clip

define('MEDIATYPE_IMAGE',        1);
define('MEDIATYPE_VIDEO',        2);
define('MEDIATYPE_AUDIO',        3);
//TODO: add "MEDIATYPE_AUDIOVIDEO"  for files containing both video & audio streams
define('MEDIATYPE_DOCUMENT',    4);
define('MEDIATYPE_WEBRESOURCE',    5);    //webresources can/will contain other files. those files will refer to this file entry as their owner

class files_default
{
    public $image_mime_types = array(
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp'
    ); ///<FIXME remove

    public $audio_mime_types    = array(
        'audio/x-mpeg',    'audio/mpeg', //.mp3 file. FF2 = 'audio/x-mpeg', IE7 = 'audio/mpeg'
        'audio/x-ms-wma',             //.wma file. FF2 & IE7 sends this
        'application/x-ogg'           //.ogg file - FIXME: IE7 sends mime header 'application/octet-stream' for .ogg
    ); ///<FIXME remove

    public $video_mime_types = array(
        'video/mpeg',      //.mpg file
        'video/avi',       //.avi file
        'video/x-msvideo', //.avi file
        'video/x-ms-wmv',  //Microsoft .wmv file
        'video/3gpp',      //.3gp video file
        'video/x-flv',     //Flash video
        'video/mp4',       //MPEG-4 video
        'video/quicktime', //Apple Quicktime .mov file
        'application/ogg'  //Ogg video
    ); ///<FIXME remove

    public $document_mime_types = array(
        'text/plain',         //normal text file
        'application/msword', //Microsoft .doc file
        'application/pdf'     //Adobe .pdf file
    ); ///<FIXME remove

    public $media_types = array(
        MEDIATYPE_IMAGE => array(
            array('image/png',  'PNG Image'),
            array('image/jpeg', 'JPEG Image'),
            array('image/gif',  'GIF Image'),
            array('image/bmp',  'BMP Image'),
        ),
        MEDIATYPE_VIDEO => array(
            array('video/x-ms-wmv', 'Windows Media Video'),
            array('video/avi',      'DivX 3 Video'),
            array('video/mpeg',     'MPEG-2 Video'),
            array('video/3gpp',     '3GP Video (cellphones)'),
            array('video/x-flv',    'Flash Video'),
            array('video/mp4',      'MPEG-4 Video'),
        ),
        MEDIATYPE_AUDIO => array(
            array('audio/x-ms-wma',    'Windows Media Audio'),
            array('audio/mpeg',        'MP3 Audio'),
            array('audio/x-mpeg',      'MP3 Audio'),
            array('application/x-ogg', 'OGG Audio'),
        ),
        MEDIATYPE_DOCUMENT => array(
            array('text/plain',         'Text Document'),
            array('application/msword', 'Word Document'),
            array('application/pdf',    'PDF Document'),
        ),
        MEDIATYPE_WEBRESOURCE => array(
            array('text/html',                'HTML Page'),
            array('application/x-bittorrent', 'BitTorrent File'),
        )
    ); ///<mimetype to media type mapping table

    /* User configurable settings */
    public $upload_dir = '/webupload/';       ///< Default upload directory
    public $tmp_dir    = '/tmp/';             ///< temp directory

    public $thumb_default_width        = 80;     ///< Default width of thumbnails
    public $thumb_default_height    = 80;     ///< Default height of thumbnails

    public $image_max_width            = 900;    ///< bigger images will be resized to this size
    public $image_max_height        = 800;

    public $anon_uploads            = false;  ///< allow unregisterd users to upload files
    public $count_file_views        = false;  ///< FIXME REMOVE! auto increments the "cnt" in tblFiles in each $files->sendFile() call
    public $apc_uploads                = false;  ///< enable support for php_apc + php_uploadprogress calls
    public $image_convert            = true;   ///< use imagemagick to handle exotic image formats

    public $process_callback        = false;  ///< script to callback on process server completition (optional)

    public $allow_rating            = false;  ///< allow file rating?
    public $allow_user_categories    = true;   ///< allow normal users to create own file categories
    public $allow_root_level        = true;   ///< shows root level of file categories, allowing users to upload there

    public $default_video = 'video/x-flv';    ///< FLV = default fileformat to convert video to
    public $default_audio = 'audio/x-mpeg';   ///< MP3 = default fileformat to convert audio to

    /**
     * Constructor. Initializes class configuration
     *
     * @param $config array of config options for the Files class
     * @return nothing
     */
    function __construct($config = '')
    {
        if (isset($config['upload_dir'])) $this->upload_dir = $config['upload_dir'];
        if (isset($config['tmp_dir']))    $this->tmp_dir = $config['tmp_dir'];

        if (isset($config['image_max_width']))      $this->image_max_width = $config['image_max_width'];
        if (isset($config['image_max_height']))     $this->image_max_height = $config['image_max_height'];
        if (isset($config['thumb_default_width']))  $this->thumb_default_width = $config['thumb_default_width'];
        if (isset($config['thumb_default_height'])) $this->thumb_default_height = $config['thumb_default_height'];

        if (isset($config['count_file_views'])) $this->count_file_views = $config['count_file_views'];
        if (isset($config['anon_uploads']))     $this->anon_uploads = $config['anon_uploads'];
        if (isset($config['apc_uploads']))      $this->apc_uploads = $config['apc_uploads'];
        if (isset($config['image_convert']))    $this->image_convert = $config['image_convert'];

        if (isset($config['allow_rating']))          $this->allow_rating = $config['allow_rating'];
        if (isset($config['allow_user_categories'])) $this->allow_user_categories = $config['allow_user_categories'];
        if (isset($config['allow_root_level']))      $this->allow_root_level = $config['allow_root_level'];

        if (isset($config['process_callback']))      $this->process_callback = $config['process_callback'];
    }

    /**
     * Returns files uploaded of the type specified
     *
     * @param $fileType filetype
     * @param $ownerId optionally select by owner also
     * @param $order optionally specify sort order
     * @param $count optionally specify how many to return
     * @return list of files
     */
    function getFileList($fileType, $ownerId = 0, $order = 'ASC', $count = 0)
    {
        global $db;
        if (!is_numeric($fileType) || !is_numeric($ownerId) || !is_numeric($count) || ($order != 'ASC' && $order != 'DESC')) return false;

        $q = 'SELECT * FROM tblFiles WHERE fileType='.$fileType;
        if ($ownerId) $q .= ' AND ownerId='.$ownerId;
        $q .= ' AND timeDeleted IS NULL';
        $q .= ' ORDER BY timeUploaded '.$order.($count ? ' LIMIT '.$count : '');
        return $db->getArray($q);
    }

    /**
     * Returns mimetype of file
     *
     * @param $filename fileId or name of file to check
     */
    function lookupMimeType($filename)
    {
        if (is_numeric($filename))
            $filename = $this->findUploadPath($filename);

        //TODO: add a lookup cache
        $result = file_get_mime($filename);

        return $result;
    }

    /**
     * Checks what kind of media this is (video, document etc)
     *
     * @param $filename fileId or name of file to check
     * @return media type id (0 for unknown media types)
     */
    function lookupMediaType($filename)
    {
        if (is_numeric($filename))
            $filename = $this->findUploadPath($filename);

        if (!file_exists($filename))
            return false;

        $mime = $this->lookupMimeType($filename);

        foreach ($this->media_types as $type_id => $row)
            foreach ($row as $subtype)
                if ($subtype[0] == $mime)
                    return $type_id;

        return 0;
    }

    /**
     * Moves a file to a different file category
     *
     * @param $_category category to move to
     * @param $_id fileId to move
     * @return true on success
     */
    function moveFile($_category, $_id)
    {
        global $db, $h;
        if (!$h->session->id || !is_numeric($_category) || !is_numeric($_id)) return false;

        $q = 'UPDATE tblFiles SET categoryId='.$_category.' WHERE fileId='.$_id;
        if (!$h->session->isAdmin) $q.= ' AND uploaderId='.$h->session->id;
        $db->update($q);

        return true;
    }

    /**
     * Marks a file as deleted
     *
     * @param $_id fileId to delete
     * @param $ownerId optionally specify owner of file
     * @param $force remove file from disk
     * @return true on success
     */
    function deleteFile($_id, $ownerId = 0, $force = false)
    {
        global $db;
        if (!is_numeric($_id) || !is_numeric($ownerId)) return false;

        if (!$this->deleteFileEntry(0, $_id, $ownerId)) return false;

        $filename = $this->findUploadPath($_id);
        if (!file_exists($filename)) return false;

        if ($force) {
            unlink($filename);
            $this->clearThumbs($_id);

            //remove file description
            deleteComments(COMMENT_FILEDESC, $_id);

            $q = 'DELETE FROM tblFiles WHERE fileId='.$_id;
            if ($ownerId) $q .= ' AND ownerId='.$ownerId;
            $db->delete($q);
        }

        return true;
    }

    /**
     * Deletes a file entry from database
     *
     * @param $_type file type
     * @param $_id fileId to delete
     * @param $ownerId optionally specify owner of file
     * @param $categoryId file category
     * @return true on success
     */
    function deleteFileEntry($_type, $_id, $ownerId = 0, $categoryId = 0)
    {
        global $db;
        if (!is_numeric($_type) || !is_numeric($_id) || !is_numeric($ownerId) || !is_numeric($categoryId)) return false;

        $q = 'UPDATE tblFiles SET timeDeleted=NOW() WHERE fileId='.$_id;
        if ($_type) $q .= ' AND fileType='.$_type;
        if ($ownerId) $q .= ' AND ownerId='.$ownerId;
        if ($categoryId) $q .= ' AND categoryId='.$categoryId;
        if ($db->update($q)) return true;
        return false;
    }

    /**
     * Deletes all file entries for specified owner
     *
     * @param $type type of file (0 for all)
     * @param $ownerId user Id
     * @return number of files deleted
     */
    function deleteFileEntries($_type, $ownerId)
    {
        global $db;
        if (!is_numeric($_type) || !is_numeric($ownerId)) return false;

        $q = 'UPDATE tblFiles SET timeDeleted=NOW() WHERE ownerId='.$ownerId;
        if ($_type) $q .= ' AND fileType='.$_type;
        return $db->update($q);
    }

    /**
     * Deletes all thumbnails for this file ID
     *
     * @param $_id file id
     * @return true on success
     */
    function clearThumbs($_id)
    {
        if (!is_numeric($_id)) return false;

        $thumbs_dir = dirname($this->findThumbPath($_id));

        $dir = scandir($thumbs_dir);
        foreach ($dir as $name)
        {
            if (strpos($name, $_id.'_') !== false) {
                unlink($thumbs_dir.'/'.$name);
            }
        }
        return true;
    }

    /**
     * Stores uploaded file associated to $h->session->id
     *
     * @param $FileData array of php internal file data from file upload
     * @param $fileType type of file
     * @param $ownerId file owner
     * @param $categoryId category where to store the file
     * @return fileId of the newly imported file
     */
    function handleUpload($FileData, $fileType, $ownerId = 0, $categoryId = 0)
    {
        global $h, $config;
        if ((!$h->session->id && !$this->anon_uploads) || !is_numeric($fileType) || !is_numeric($ownerId) || !is_numeric($categoryId)) return false;

        //ignore empty file uploads
        if (!$FileData['name']) return;

        if (!is_uploaded_file($FileData['tmp_name'])) {
            $h->session->error = t('Uploaded file is too big');
            $h->session->log('Attempt to upload too big file');
            return false;
        }

        $FileData['type'] = $this->lookupMimeType($FileData['tmp_name']);    //internal mimetype sucks!

        $fileId = $this->addFileEntry($fileType, $categoryId, $ownerId, $FileData['name']);

        //Identify and handle various types of files
        if (in_array($FileData['type'], $this->image_mime_types)) {
            $this->handleImageUpload($fileId, $FileData);
        } else if (in_array($FileData['type'], $this->video_mime_types)) {
            $this->handleVideoUpload($fileId, $FileData);
        } else if (in_array($FileData['type'], $this->audio_mime_types)) {
            //FIXME audio conversion with process server
            $this->moveUpload($FileData['tmp_name'], $fileId);
        } else {
            $this->moveUpload($FileData['tmp_name'], $fileId);
        }

        $this->updateFile($fileId);    //force update of filesize, mimetype & checksum

        if ($fileType == FILETYPE_USERFILE) {
            addToModerationQueue(MODERATION_FILE, $fileId, true);

            //notify subscribers
            if (!empty($config['subscriptions']['notify'])) {
                notifySubscribers(SUBSCRIPTION_FILES, $ownerId, $fileId);
            }
        }
        if ($fileType == FILETYPE_USERDATA) {
            addToModerationQueue(MODERATION_PRES_IMAGE, $fileId, true);
        }

        return $fileId;
    }

    /**
     * Adds a new entry for a new file in the database
     *
     * @param $fileType
     * @param $categoryId
     * @param $ownerId
     * @param $fileName
     * @param $content
     * @return fileId from the database entry created, or false on failure
     */
    function addFileEntry($fileType, $categoryId, $ownerId, $fileName, $content = '')
    {
        global $db, $h;
        if (!is_numeric($fileType) || !is_numeric($categoryId) || !is_numeric($ownerId)) return false;

        $fileSize = 0;
        $fileMime = '';
        $fileName = basename(strip_tags($fileName));

        if ($h && $h->session) {
            $q = 'INSERT INTO tblFiles SET fileName="'.$db->escape($fileName).'",ownerId='.$ownerId.',uploaderId='.$h->session->id.',uploaderIP='.$h->session->ip.',timeUploaded=NOW(),fileType='.$fileType.',categoryId='.$categoryId;
        } else {
            $q = 'INSERT INTO tblFiles SET fileName="'.$db->escape($fileName).'",ownerId='.$ownerId.',uploaderId=0,uploaderIP=0,timeUploaded=NOW(),fileType='.$fileType.',categoryId='.$categoryId;
        }
        $newFileId = $db->insert($q);

        if ($content) {
            file_put_contents($this->findUploadPath($newFileId), $content);
        }

        $this->updateFile($newFileId);

          return $newFileId;
    }

    /**
     * Moves uploaded file to correct directory
     */
    function moveUpload($tmp_name, $fileId)
    {
        //Move the uploaded file to upload directory
        $uploadfile = $this->findUploadPath($fileId);
        if (move_uploaded_file($tmp_name, $uploadfile)) {
            chmod($uploadfile, 0777);
            return true;
        }
        dp('Failed to move file from '.$tmp_name.' to '.$uploadfile);
        return false;
    }

    /**
     * Finds out where to store the file in filesystem, creating directories when nessecary
     */
    function findUploadPath($fileId, $mkdir = true, $base_dir = '')
    {
        $subdir = floor($fileId / 10000) * 10000;

        if ($mkdir && !is_dir($this->upload_dir)) {
            mkdir($this->upload_dir);
            chmod($this->upload_dir, 0777);
        }

        if (!$base_dir) $base_dir = 'org/';
        $dir = $this->upload_dir.$base_dir.$subdir;

        if ($mkdir && !is_dir($this->upload_dir.$base_dir)) {
            mkdir($this->upload_dir.$base_dir);
            chmod($this->upload_dir.$base_dir, 0777);
        }

        if ($mkdir && !is_dir($dir)) {
            mkdir($dir);
            chmod($dir, 0777);
        }

        return $dir.'/'.$fileId;
    }

    function findThumbPath($fileId)
    {
        return $this->findUploadPath($fileId, true, 'thumb/');
    }

    /**
     * If server is configured for process server use,
     * enqueue the video for conversion, otherwise just store it away
     */
    function handleVideoUpload($fileId, $FileData)
    {
        global $db, $config;

        $this->moveUpload($FileData['tmp_name'], $fileId);

        if ($this->default_video) {
            if ($FileData['type'] != $this->default_video) {

                $uri = $config['app']['full_url'].coredev_webroot().'api/file.php?id='.$fileId;

                $client = new SoapClient($config['process']['soap_server']);
                try {
                    $callback_uri = $this->process_callback.(strpos($this->process_callback, '?') !== false ? '&' : '?').'id='.$fileId;

                    $refId = $client->fetchAndConvert($config['process']['username'], $config['process']['password'], $uri, $callback_uri, '');
                    if (!$refId) echo 'Failed to add order!';

                } catch (Exception $e) {
                    echo 'Exception: '.$e.'<br/><br/>';
                }
            }
        }

        return true;
    }

    /**
     * Handle image upload, used internally only
     *
     * @param $fileId file id to deal with
     * @param $FileData array of php internal file data from file upload
     */
    function handleImageUpload($fileId, $FileData)
    {
        global $db;

        switch ($FileData['type']) {
            case 'image/bmp':    //IE 7, Firefox 2, Opera 9.2
                if (!$this->image_convert) break;
                $out_tempfile = $this->tmp_dir.'core_outfile.jpg';
                $check = convertImage($FileData['tmp_name'], $out_tempfile, 'image/jpeg');
                if (!$check) {
                    dp('Failed to convert bmp to jpeg!');
                    break;
                }

                unlink($FileData['tmp_name']);
                rename($out_tempfile, $FileData['tmp_name']);
                $filesize = filesize($FileData['tmp_name']);
                $q = 'UPDATE tblFiles SET fileMime="image/jpeg", fileName="'.$db->escape(basename(strip_tags($FileData['name']))).'.jpg",fileSize='.$filesize.' WHERE fileId='.$fileId;
                $db->update($q);
                break;
        }

        list($img_width, $img_height) = getimagesize($FileData['tmp_name']);

        //Resize the image if it is too big, overwrite the uploaded file
        if (($img_width > $this->image_max_width) || ($img_height > $this->image_max_height))
        {
            resizeImageExact($FileData['tmp_name'], $FileData['tmp_name'], $this->image_max_width, $this->image_max_height, $fileId);
        }

        $this->moveUpload($FileData['tmp_name'], $fileId);
        $this->makeThumbnail($fileId);
        return true;
    }

    /**
     * Generates a thumbnail for given image file
     */
    function makeThumbnail($fileId)
    {
        if (!is_numeric($fileId)) return false;

        //create default sized thumbnail
        $thumb_filename = $this->findThumbPath($fileId).'_'.$this->thumb_default_width.'x'.$this->thumb_default_height;
        resizeImageExact($this->findUploadPath($fileId), $thumb_filename, $this->thumb_default_width, $this->thumb_default_height);
    }

    /**
     * Returns checksums for specified file.
     * If checksums were already generated, it fetches them from tblChecksums
     *
     * @param $_id fileId to get checksums for
     * @param $force if set to true the db cache of checksums is ignored
     * @param $update if set updates database, otherwise just return checksum array
     * @return checksums in array
     */
    function checksums($_id, $force = false, $update = true)
    {
        global $db;
        if (!is_numeric($_id)) return false;

        $q = 'SELECT * FROM tblFiles WHERE fileId='.$_id.' AND timeDeleted IS NULL';
        $data = $db->getOneRow($q);
        if (!$data) return false;

        $q = 'SELECT * FROM tblChecksums WHERE fileId='.$_id;
        $cached = $db->getOneRow($q);
        if (!$force && $cached) return $cached;

        $filename = $this->findUploadPath($_id);

        if (!file_exists($filename)) {
            die('tried to generate checksums of nonexisting file '.$filename);
        }

        $exec_start = microtime(true);
        $new['sha1'] = $db->escape(hash_file('sha1', $filename));    //40-character hex string
        $new['md5'] = $db->escape(hash_file('md5', $filename));        //32-character hex string
        $new['timeCreated'] = now();
        $exec_time = microtime(true) - $exec_start;
        $new['timeExec'] = $exec_time;

        if ($update) {
            $q = 'DELETE FROM tblChecksums WHERE fileId='.$_id;
            $db->delete($q);

            $q = 'INSERT INTO tblChecksums SET fileId='.$_id.', sha1="'.$new['sha1'].'", md5="'.$new['md5'].'", timeExec="'.$new['timeExec'].'", timeCreated=NOW()';
            $db->insert($q);
        }

        return $new;
    }

    /**
     * Returns sha1 checksum of file $_id. forces checksum generation if missing
     *
     * @param $_id fileId
     * @return sha1-sum
     */
    function sha1($_id)
    {
        $sums = $this->checksums($_id);
        return $sums['sha1'];
    }

    /**
     * Used for file processing. generates a new file entry referencing to entry $_id. returns new id
     *
     * @param $_id fileId
     * @param $_clone_type filetype of clone
     * @return fileId of the clone
     */
    function cloneFile($_id, $_clone_type)
    {
        global $db;
        if (!is_numeric($_id) || !is_numeric($_clone_type)) return false;

        $file = $this->getFile($_id);
        if (!$file) return false;

        $q = 'INSERT INTO tblFiles SET ownerId='.$_id.',fileType='.$_clone_type.',uploaderId='.$file['uploaderId'].',timeUploaded=NOW()';
        return $db->insert($q);
    }

    /**
     * Forces recalculation of filesize, mimetype and checksums
     *
     * @param $_id
     * @return numeric value on true, else 0 or false
     */
    function updateFile($_id)
    {
        global $db;
        if (!is_numeric($_id)) return false;

        $filename = $this->findUploadPath($_id, false);
        if (!file_exists($filename)) return false;

        clearstatcache();    //needed to get current filesize()

        $size = filesize($filename);
        if (!is_numeric($size) || !$size) return false;

        $mime_type  = $this->lookupMimeType($filename);
        $media_type = $this->lookupMediaType($filename);

        //force calculation of checksums
        $this->checksums($_id, true);

        //parse result such as: text/plain; charset=us-ascii
        $arr = explode(';', $mime_type);
        $mime_type = $arr[0];

        $q = 'UPDATE tblFiles SET fileMime="'.$db->escape($mime_type).'",mediaType='.$media_type.',fileSize='.$size.' WHERE fileId='.$_id;
        $db->update($q);
        return true;
    }

    function imageResize($_id, $_pct)
    {
        global $db, $h;
        if (!$h->session->id || !is_numeric($_id) || !is_numeric($_pct)) return false;

        $data = $db->getOneRow('SELECT * FROM tblFiles WHERE fileId='.$_id);
        if (!$data) return false;

        if (!in_array($data['fileMime'], $this->image_mime_types)) return false;

        header('Content-Type: '.$data['fileMime']);
        header('Content-Disposition: inline; filename="'.basename($data['fileName']).'"');
        header('Content-Transfer-Encoding: binary');

        $filename = $this->findUploadPath($_id);

        resizeImage($filename, $filename, $_pct);
        http_cached_headers(false);
        $this->sendImage($_id);

        $this->clearThumbs($_id);
        $this->makeThumbnail($_id);
    }

    function imageCrop($_id, $x1, $y1, $x2, $y2)
    {
        global $h;
        if (!$h->session->id || !is_numeric($_id) || !is_numeric($x1) || !is_numeric($y1) || !is_numeric($x2) || !is_numeric($y2)) return false;

        $filename = $this->findUploadPath($_id);
        cropImage($filename, $filename, $x1, $y1, $x2, $y2);
        http_cached_headers(false);
        $this->sendImage($_id);

        $this->clearThumbs($_id);
        $this->makeThumbnail($_id);
    }

    /**
     * Performs an image rotation and then pass on the result to the user
     *
     * @param $_id fileId
     * @param $_angle how much to rotate the image
     */
    function imageRotate($_id, $_angle)
    {
        global $db, $h;
        if (!$h->session->id || !is_numeric($_id) || !is_numeric($_angle)) return false;

        $data = $db->getOneRow('SELECT * FROM tblFiles WHERE fileId='.$_id);
        if (!$data) return false;

        if (!in_array($data['fileMime'], $this->image_mime_types)) return false;

        header('Content-Type: '.$data['fileMime']);
        header('Content-Disposition: inline; filename="'.basename($data['fileName']).'"');
        header('Content-Transfer-Encoding: binary');

        $filename = $this->findUploadPath($_id);

        rotateImage($filename, $filename, $_angle);
        http_cached_headers(false);
        $this->sendImage($_id);

        $this->clearThumbs($_id);
        $this->makeThumbnail($_id);
    }

    /**
     * Takes get parameter 'dl' to send the file as an attachment
     *
     * @param $_id fileId
     * @param $force_mime
     */
    function sendFile($_id, $force_mime = false)
    {
        global $db;
        if (!is_numeric($_id)) return false;

        $q = 'SELECT * FROM tblFiles WHERE fileId='.$_id.' AND timeDeleted IS NULL';
        $data = $db->getOneRow($q);
        if (!$data) return false;

        if (isset($_GET['dl'])) {
            /* Prompts the user to save the file */
            header('Content-Disposition: attachment; filename="'.basename($data['fileName']).'"');
        } else {
            /* Displays the file in the browser, and assigns a filename for the browser's "save as..." features */
            header('Content-Disposition: inline; filename="'.basename($data['fileName']).'"');
        }

        header('Content-Transfer-Encoding: binary');

        //Serves the file differently depending on what kind of file it is
        if ($data['mediaType'] == MEDIATYPE_IMAGE) {
            //Generate resized image if needed
            $this->sendImage($_id);
        } else {
            http_cached_headers();

            /* This sends files without extension etc as plain text if you didnt specify to download them */
            if ((!$force_mime && !isset($_GET['dl'])) || $data['fileMime'] == 'application/octet-stream') {
                header('Content-Type: text/plain; charset="UTF-8"');
            } else {
                header('Content-Type: '.$data['fileMime']);
            }

            if ($data['fileSize']) header('Content-Length: '. $data['fileSize']);

            //Deliver the file as-is
            readfile($this->findUploadPath($_id));
        }

        //Count the file downloads
        if ($this->count_file_views) {
            $db->update('UPDATE tblFiles SET cnt=cnt+1 WHERE fileId='.$_id);
        }

        return true;
    }

    /**
     * Send image to user
     *
     * Optional parametera:
     * $_GET['w'] width
     * $_GET['h'] height
     *
     * @param $_id fileId
     */
    function sendImage($_id)
    {
        global $h;

        $filename = $this->findUploadPath($_id);
        if (!file_exists($filename)) die('file not found');

        $temp = getimagesize($filename);

        $img_width = $temp[0];
        $img_height = $temp[1];
        $mime_type = $temp['mime'];

        $width = 0;
        $log = true;
        if (!empty($_GET['w']) && is_numeric($_GET['w'])) {
            $log = false;
            $width = $_GET['w'];
        }
        if ($width < 10 || $width > 1500) $width = 0;

        $height = 0;
        if (!empty($_GET['h']) && is_numeric($_GET['h'])) {
            $log = false;
            $height = $_GET['h'];
        }
        if ($height < 10 || $height > 1500) $height = 0;

        if (!$width || !$height || $width == $img_width || $height == $img_height) {
            $out_filename = $filename;
        } else {
            //Look for cached thumbnail
            $out_filename = $this->findThumbPath($_id).'_'.$width.'x'.$height;

            if (!file_exists($out_filename)) {
                //Thumbnail of this size dont exist, create one
                resizeImageExact($filename, $out_filename, $width, $height);
            }
        }

        if ($h && filemtime($out_filename) < $h->session->started) {
            http_cached_headers(true);
        } else {
            http_cached_headers(false);
        }
        header('Content-Type: '.$mime_type);
        header('Content-Length: '.filesize($out_filename));

        readfile($out_filename);

        if ($log) {
            logVisit(VISIT_FILE, $_id);
        }
    }

    /**
     * Selects all files for specified type & owner
     *
     * @param $fileType type of files
     * @param $ownerId owner of the files
     * @param $categoryId category of the files
     * @param $_limit optional limit the result
     * @param $_order optional return order ASC or DESC (timeUploaded ASC default)
     */
    function getFiles($fileType = 0, $ownerId = 0, $categoryId = 0, $_limit = '', $_order = 'ASC')
    {    //FIXME: remove function & rename getFilesByMediaType() to getFiles() instead!
        global $db, $h;
        if (!is_numeric($fileType) || !is_numeric($ownerId) || !is_numeric($categoryId)) return false;
        if ($_order != 'ASC' && $_order != 'DESC') return false;

        if ($fileType == FILETYPE_CLONE_VIDEOTHUMB10 ||
            $fileType == FILETYPE_VIDEOBLOG) {
            $q  = 'SELECT * FROM tblFiles';
            $q .= ' WHERE fileType='.$fileType;
            if ($categoryId) $q .= ' AND categoryId='.$categoryId;
            if ($ownerId) $q .= ' AND ownerId='.$ownerId;
            $q .= ' AND timeDeleted IS NULL';
            $q .= ' ORDER BY timeUploaded '.$_order;

        } else if ($h->session && $h->session->id && $fileType == FILETYPE_FORUM) {
            $q  = 'SELECT * FROM tblFiles';
            $q .= ' WHERE fileType='.$fileType.' AND uploaderId='.$h->session->id;
            if ($ownerId) $q .= ' AND ownerId='.$ownerId;
            $q .= ' AND timeDeleted IS NULL';
            $q .= ' ORDER BY timeUploaded '.$_order;

        } else if ($fileType) {
            $q  = 'SELECT t1.*,t2.userName AS uploaderName FROM tblFiles AS t1 ';
            $q .= ' LEFT JOIN tblUsers AS t2 ON (t1.uploaderId=t2.userId)';
            $q .= ' WHERE t1.categoryId='.$categoryId;
            if ($ownerId) $q .= ' AND t1.ownerId='.$ownerId;
            $q .= ' AND t1.fileType='.$fileType;
            $q .= ' AND t1.timeDeleted IS NULL';
            $q .= ' ORDER BY t1.timeUploaded '.$_order;
        } else {
            $q = 'SELECT * FROM tblFiles';
            $q .= ' WHERE timeDeleted IS NULL';
        }

        if ($_limit) $q .= ' LIMIT 0,'.$_limit;

        return $db->getArray($q);
    }

    /**
     * Get file count
     *
     * @param $fileType type of files
     * @param $ownerId owner of the files (optional)
     * @param $categoryId category of the files (optional)
     * @param $media_type type of media (optional)
     */
    function getFileCount($fileType = 0, $ownerId = 0, $categoryId = 0, $media_type = 0)
    {
        global $db;
        if (!is_numeric($fileType) || !is_numeric($ownerId) || !is_numeric($categoryId) || !is_numeric($media_type)) return 0;

        $q = 'SELECT COUNT(fileId) FROM tblFiles';
        $q .= ' WHERE timeDeleted IS NULL';
        if ($fileType) $q .= ' AND fileType='.$fileType;
        if ($categoryId) $q .= ' AND categoryId='.$categoryId;
        if ($ownerId) $q .= ' AND ownerId='.$ownerId;
        if ($media_type) $q .= ' AND mediaType='.$media_type;
        return $db->getOneItem($q);
    }

    /**
     * Get the number of new files uploaded during the specified time period
     */
    function getFilesCountPeriod($dateStart, $dateStop)
    {
        global $db;

        $q = 'SELECT count(fileId) AS cnt FROM tblFiles WHERE timeUploaded BETWEEN "'.$db->escape($dateStart).'" AND "'.$db->escape($dateStop).'"';
        return $db->getOneItem($q);
    }

    /**
     * Retrieves detailed info about the specified file
     *
     * @param $_id fileId
     */
    function getFileInfo($_id)
    {
        global $db;
        if (!is_numeric($_id) || !$_id) return false;

        $q = 'SELECT t1.*,t2.userName AS uploaderName FROM tblFiles AS t1';
        $q .= ' LEFT JOIN tblUsers AS t2 ON (t1.uploaderId=t2.userId)';
        $q .= ' WHERE t1.fileId='.$_id.' AND t1.timeDeleted IS NULL';

        return $db->getOneRow($q);
    }

    /**
     * Retrieves info about the specified file
     *
     * @param $_id fileId
     */
    function getFile($_id)
    {
        global $db;
        if (!is_numeric($_id) || !$_id) return false;

        $q = 'SELECT * FROM tblFiles WHERE fileId='.$_id.' AND timeDeleted IS NULL';
        return $db->getOneRow($q);
    }

    /**
     * Shows attachments. used to show files attached to a forum post
     *
     * @param $_type type of file
     * @param $_owner owner of the files
     */
    function showAttachments($_type, $_owner)
    {
        global $config;

        $list = $this->getFiles($_type, $_owner);

        if (count($list)) {
            echo '<hr/>';
            echo 'Attached files:<br/>';
            foreach ($list as $row) {
                $show_text = $row['fileName'].' ('.formatDataSize($row['fileSize']).')';
                echo '<a href="'.coredev_webroot().'api/file_pt.php?id='.$row['fileId'].'" target="_blank">';
                if (in_array($row['fileMime'], $this->image_mime_types)) {
                    echo makeThumbLink($row['fileId'], $show_text).'</a> ';
                } else {
                    echo $show_text.'</a><br/>';
                }
            }
        }
    }

    /**
     * Returns user who uploaded specified file
     *
     * @param $_id fileId
     */
    function getUploader($_id)
    {
        global $db;
        if (!is_numeric($_id)) return false;

        $q = 'SELECT uploaderId FROM tblFiles WHERE fileId='.$_id;
        return $db->getOneItem($q);
    }

    /**
     * Returns owner of specified file
     *
     * @param $_id fileId
     */
    function getOwner($_id)
    {
        global $db;
        if (!is_numeric($_id)) return false;

        $q = 'SELECT ownerId FROM tblFiles WHERE fileId='.$_id;
        return $db->getOneItem($q);
    }

    /**
     * Returns category of specified file
     *
     * @param $_id fileId
     */
    function getCategory($_id)
    {
        global $db;
        if (!is_numeric($_id)) return false;

        $q = 'SELECT categoryId FROM tblFiles WHERE fileId='.$_id;
        return $db->getOneItem($q);
    }

    /**
     * Returns a list of file entries
     *
     * @param $fileType type of files (wiki file, user file etc)
     * @param $ownerId owner of the files
     * @param $categoryId category of the files
     * @param $mediaType media type of file (image, audio etc)
     * @param $_limit optional limit the result
     * @param $_order optional return order ASC or DESC (timeUploaded ASC default)
     */
    function getFilesByMediaType($fileType = 0, $ownerId = 0, $categoryId = 0, $mediaType = 0, $_limit = '', $_order = 'ASC')
    {    //FIXME rename to getFiles(), remove old getFiles() & clean up parameter usage for getFiles() everywhere
        global $db;
        if (!is_numeric($fileType) || (!is_numeric($ownerId) && !is_array($ownerId)) || !is_numeric($categoryId) || !is_numeric($mediaType)) return false;
        if ($_order != 'ASC' && $_order != 'DESC') return false;
        $q = 'SELECT * FROM tblFiles';
        $q .= ' WHERE timeDeleted IS NULL';
        if ($fileType) $q .= ' AND fileType='.$fileType;
        if (is_numeric($ownerId) && $ownerId) $q .= ' AND ownerId='.$ownerId;
        else if (is_array($ownerId)) {
            $q .= ' AND (';
            foreach ($ownerId as $uid) {
                if (is_numeric($uid)) $q .= 'ownerId='.$uid.' OR ';
            }
            $q = substr($q, 0, -4); // remove extra ' OR '
            $q .= ')';
        }
        if ($categoryId) $q .= ' AND categoryId='.$categoryId;
        if ($mediaType) $q .= ' AND mediaType='.$mediaType;
        $q .= ' ORDER BY timeUploaded '.$_order;
        if ($_limit) {
            if (is_numeric($_limit)) $q .= ' LIMIT 0,'.$_limit;
            else $q .= $_limit;
        }

        return $db->getArray($q);
    }
}


/**
 * @enable enable or disable cache headers?
 */
function http_cached_headers($enable = true)  //XXX DEPRECATE, only used by files_default which will be deprecated
{
    if ($enable) {
        //Tell browser to cache the output for 30 days. Works with MSIE6 and Firefox 1.5
        header('Expires: ' . date("D, j M Y H:i:s", time() + (86400 * 30)) . ' UTC');
        header('Cache-Control: Public');
        header('Pragma: Public');
    } else {
        //Force browser to not cache content
        header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    }
}
?>
