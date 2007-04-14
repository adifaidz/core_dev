<?
/*
	Files class - Handle file upload, image manipulating, file management
	
	Uses tblFiles & tblCategories

	Written by Martin Lindhe, 2007

	todo:
		- cleanup, vissa funktioner �r nog �verfl�diga. se igenom all kod

	todo file viewern:
		- all bakgrund blir utgr�ad (todo: bilden blir �x� transparent)
			kika bildvisare: http://www.mameworld.net/gurudumps/
			i detta l�ge visas �ven en rad knappar p� botten: rotate, resize, save, previous, next, start/stop slideshow image

		- visa en papperskorg, kunna drag-droppa en fil och sl�ppa i papperskorgen (ta bort med ajax)

		- ajax file upload, submitta file-formul�ret i bakgrunden,n�r man f�r "ok" svar fr�n servern,
			s� l�ggs en <div> till som visar thumbnail p� nyss uppladdad bild.

		- visa animerad gif medans filen laddas upp (senare: progress meter)
*/

require_once('functions_files.php');
require_once('functions_general.php');

define('CATEGORY_TYPE_FILES', 1);

define('FILETYPE_WIKI',						100); /* File is attached to a wiki */
define('FILETYPE_PR',							101);	/* File is attached to a PR */
define('FILETYPE_BLOG',						102);	/* File is attached to a blog */
define('FILETYPE_PHOTOALBUM',			103);	/* File is uploaded to a photoalbum */
define('FILETYPE_USERDATAFIELD',	104); /* File belongs to a userdata field */
define('FILETYPE_FILEAREA_UPLOAD',105);	/* File is uploaded to a file area */

class Files
{
	private $upload_dir = 'e:/devel/webupload/';						//	'/tmp/';
	private $thumbs_dir = 'e:/devel/webupload/thumbs/';		//	'/tmp/';
	public $allowed_image_types	= array('jpg', 'jpeg', 'png', 'gif');
	public $allowed_audio_types	= array('mp3');

	private $image_max_width			= 800;	//bigger images will be resized to this size	
	private $image_max_height			= 600;
	private $thumb_default_width	= 100;
	private $thumb_default_height	= 80;
	private $image_jpeg_quality		= 70;		//0-100% quality for recompression of very large uploads (like digital camera pictures)
	private $resample_resized			= true;	//use imagecopyresampled() instead of imagecopyresized() to create better-looking thumbnails
	private $count_file_views			= false;	//auto increments the "cnt" in tblFiles in each $files->sendFile() call

	function __construct(array $files_config)
	{
		if (isset($files_config['upload_dir'])) $this->upload_dir = $files_config['upload_dir'];
		if (isset($files_config['thumbs_dir'])) $this->thumbs_dir = $files_config['thumbs_dir'];
		if (isset($files_config['allowed_image_types'])) $this->allowed_image_types = $files_config['allowed_image_types'];
		if (isset($files_config['allowed_audio_types'])) $this->allowed_audio_types = $files_config['allowed_audio_types'];
		
		if (isset($files_config['image_max_width'])) $this->image_max_width = $files_config['image_max_width'];
		if (isset($files_config['image_max_height'])) $this->image_max_height = $files_config['image_max_height'];
		if (isset($files_config['thumb_default_width'])) $this->thumb_default_width = $files_config['thumb_default_width'];
		if (isset($files_config['thumb_default_height'])) $this->thumb_default_height = $files_config['thumb_default_height'];

		if (isset($files_config['count_file_views'])) $this->count_file_views = $files_config['count_file_views'];
	}


	//Visar alla filer som �r uppladdade i en publik "filarea" (FILETYPE_FILEAREA_UPLOAD)
	//Eller alla filer som tillh�r en wiki (FILETYPE_WIKI)
	function showFiles($fileType, $categoryId = 0)
	{
		global $session, $db, $config;

		if (!$session->id || !is_numeric($fileType) || !is_numeric($categoryId)) return;

		require_once($config['core_root'].'layout/image_zoom_layer.html');
		require_once($config['core_root'].'layout/ajax_loading_layer.html');

		if ($fileType == FILETYPE_FILEAREA_UPLOAD) {
			$categoryId = 0;
			if (!empty($_GET['file_gadget_category_id']) && is_numeric($_GET['file_gadget_category_id'])) $categoryId = $_GET['file_gadget_category_id'];
		}

		if (!empty($_FILES['file1'])) {
			$this->handleUpload($_FILES['file1'], $fileType, $categoryId);
			if ($fileType == FILETYPE_WIKI) {
				addRevision(REVISIONS_WIKI, $categoryId, 'File uploaded...', now(), $session->id, REV_CAT_FILE_UPLOADED);
			}
		}
		
		if (!$categoryId && !empty($_POST['new_file_category']) && !empty($_POST['new_file_category_global']))
		{
			//Create new category. Only allow categories inside root level
			$this->createCategory($_POST['new_file_category'], $_POST['new_file_category_global']);
		}

		echo '<div class="file_gadget">';

		//menu
		echo '<div class="file_gadget_header">';
		echo 'File Upload Overview - Displaying ';
		switch ($fileType)
		{
			case FILETYPE_FILEAREA_UPLOAD:
				if (!$categoryId) echo 'Root Level content';
				else echo $this->getCategoryName($categoryId).' content';
				break;
			case FILETYPE_WIKI:
				echo 'wiki attachments';
				break;
		}
		echo '</div>';

		//Visar kategorier / kataloger
		if ($fileType==FILETYPE_FILEAREA_UPLOAD) {
			if (!$categoryId) {
				$cat_list = $db->GetArray('SELECT * FROM tblCategories WHERE (ownerId='.$session->id.' OR globalCategory=1) AND categoryType='.CATEGORY_TYPE_FILES);
				if (!empty($cat_list)) {
					echo 'Categories:<br/>';
					for ($i=0; $i<count($cat_list); $i++) {
						echo '<a href="?file_gadget_category_id='.$cat_list[$i]['categoryId'].'">'.$cat_list[$i]['categoryName'].'</a><br/>';
					}
					echo '<br/>';
				}
			} else {
				echo '<a href="?file_gadget_category_id=0">Go back to root level</a><br/><br/>';
			}
		}

		switch ($fileType)
		{
			case FILETYPE_FILEAREA_UPLOAD:
				$q = 'SELECT * FROM tblFiles WHERE categoryId='.$categoryId.' AND fileType='.$fileType.' ORDER BY timeUploaded ASC';
				$action = '?file_gadget_category_id='.$categoryId;
				break;

			case FILETYPE_WIKI:
				$q = 'SELECT * FROM tblFiles WHERE categoryId='.$categoryId.' AND fileType='.$fileType.' ORDER BY timeUploaded ASC';
				$action = '';
				break;
		}

		//select the files in the current category (or root level for uncategorized files)
		$list = $db->GetArray($q);

		echo '<div class="file_gadget_content">';
		for ($i=0; $i<count($list); $i++)
		{
			list($file_firstname, $file_lastname) = explode('.', strtolower($list[$i]['fileName']));

			if (in_array($file_lastname, $this->allowed_image_types)) {
				//show thumbnail of image
				echo '<div class="file_gadget_entry" id="file_'.$list[$i]['fileId'].'" onclick="zoomImage('.$list[$i]['fileId'].');"><center>';
				echo '<img src="/core/file.php?id='.$list[$i]['fileId'].'&amp;w='.$this->thumb_default_width.'&amp;h='.$this->thumb_default_height.getProjectPath().'" alt="Thumbnail" title="'.$list[$i]['fileName'].'"/>';
				echo '</center></div>';
			} else if (in_array($file_lastname, $this->allowed_audio_types)) {
				//show icon for audio files
				echo '<div class="file_gadget_entry" id="file_'.$list[$i]['fileId'].'" onclick="zoomAudio('.$list[$i]['fileId'].',\''.$list[$i]['fileName'].'\');"><center>';
				echo '<img src="/gfx/icon_audio_32.png" width="80" height="80" alt="Audio file" title="'.$list[$i]['fileName'].'"/>';
				echo '</center></div>';
			} else {
				die('todo: '. $list[$i]['fileMime']);
			}
		}
		echo '</div>';

		echo '<div id="file_gadget_upload">';
		if (!$categoryId) {
			echo '<input type="button" class="button" value="New category" onclick="show_element_by_name(\'file_gadget_category\'); hide_element_by_name(\'file_gadget_upload\');"/><br/>';
		}
		echo '<form name="ajax_show_files" method="post" action="'.$action.'" enctype="multipart/form-data">';
		echo '<input type="file" name="file1"/> ';
		echo '<input type="submit" class="button" value="Upload"/>';
		echo '</form>';
		echo '</div>';
		
		if (!$categoryId) {
			echo '<div id="file_gadget_category" style="display: none;">';
			echo '<form name="new_file_category" method="post" action="">';
			echo 'Category name: <input type="text" name="new_file_category"/> ';
			if ($session->isSuperAdmin) {
				echo '<br/>';
				echo '<input type="hidden" name="new_file_category_global" value="0"/>';
				echo '<input type="checkbox" value="1" name="new_file_category_global" id="new_file_category_global"/> ';
				echo '<label for="new_file_category_global">Make this category globally available</label><br/><br/>';
			}
			echo '<input type="submit" class="button" value="Create"/> ';
			echo '<input type="button" class="button" value="Cancel" onclick="show_element_by_name(\'file_gadget_upload\'); hide_element_by_name(\'file_gadget_category\');"/>';
			echo '</form>';
			echo '</div>';
		}
		
		echo '</div>';
	}

	/* Visar bara thumbnails. klicka en thumbnail f�r att visa hela bilden i 'image_big' div:en */
	function showThumbnails($fileType, $categoryId)
	{
		global $session, $db;
		
		if (!is_numeric($fileType)) return false;

		$list = $db->GetArray('SELECT * FROM tblFiles WHERE categoryId='.$categoryId.' AND fileType='.$fileType.' ORDER BY timeUploaded ASC');
		
		if (!$list) {
			echo 'No thumbnails to show!';
			return;
		}

		echo '<div id="image_big_holder"><div id="image_big"><img src="/core/file.php?id='.$list[0]['fileId'].getProjectPath().'" alt=""/></div></div>';
		echo '<div id="image_thumbs_scroll_up" onclick="scroll_element_content(\'image_thumbs_scroller\', -'.($this->thumb_default_height*3).');"></div>';
		echo '<div id="image_thumbs_scroll_down" onclick="scroll_element_content(\'image_thumbs_scroller\', '.($this->thumb_default_height*3).');"></div>';
		echo '<div id="image_thumbs_scroller">';

		echo '<div class="thumbnails_gadget">';
		for ($i=0; $i<count($list); $i++)
		{
			list($file_firstname, $file_lastname) = explode('.', strtolower($list[$i]['fileName']));

			//show thumbnail of image
			if (in_array($file_lastname, $this->allowed_image_types)) {
				echo '<div class="thumbnails_gadget_entry" id="thumb_'.$list[$i]['fileId'].'" onclick="loadImage('.$list[$i]['fileId'].', \'image_big\');"><center>';
				echo '<img src="/core/file.php?id='.$list[$i]['fileId'].'&amp;w='.$this->thumb_default_width.'&amp;h='.$this->thumb_default_height.getProjectPath().'" alt="Thumbnail" title="'.$list[$i]['fileName'].'"/>';
				echo '</center></div>';
			}
		}

		echo '</div>';
		echo '</div>';
	}
	
	/* Creates a new category to store files in */
	private function createCategory($categoryName, $globalCategory = 0)
	{
		global $db, $session;
		if (!$session->id || !is_numeric($globalCategory)) return false;

		if ($globalCategory && !$session->isSuperAdmin) $globalCategory = 0;

		$enc_catname = $db->escape(trim(strip_tags($categoryName)));
		if (!$enc_catname) return false;

		$sql = 'INSERT INTO tblCategories SET categoryName="'.$enc_catname.'",categoryType='.CATEGORY_TYPE_FILES.',timeCreated=NOW(),globalCategory='.$globalCategory.',ownerId='.$session->id;
		$db->query($sql);
	}
	
	function getCategoryName($_id)
	{
		global $db;
		
		if (!is_numeric($_id)) return false;
	
		$q = 'SELECT categoryName FROM tblCategories WHERE categoryId='.$_id.' AND categoryType='.CATEGORY_TYPE_FILES;
		return $db->getOneItem($q);
	}
	
	function deleteFile($_id)
	{
		global $db, $session;
		if (!$session->id || !is_numeric($_id)) return false;

		$q = 'DELETE FROM tblFiles WHERE fileId='.$_id.' AND ownerId='.$session->id;
		$db->query($q);

		//physically remove the file from disk
		unlink($this->upload_dir.$_id);

		//todo: also remove generated thumbnails
	}
	

	/* Stores uploaded file associated to $session->id */
	private function handleUpload($FileData, $fileType, $categoryId = 0)
	{
		global $db, $session;
		if (!$session->id || !is_numeric($fileType) || !is_numeric($categoryId)) return false;
		
		//ignore empty file uploads
		if (!$FileData['name']) return;

		if (!is_uploaded_file($FileData['tmp_name'])) {
			$session->error = 'File too big';
			$db->log('Attempt to upload too big file');
			return false;
		}

		$enc_filename = $db->escape(basename(strip_tags($FileData['name'])));
		$enc_mimetype = $db->escape(strip_tags($FileData['type']));

		list($file_firstname, $file_lastname) = explode('.', strtolower($enc_filename));

		$filesize = filesize($FileData['tmp_name']);

  	$sql = 'INSERT INTO tblFiles SET fileName="'.$enc_filename.'",fileSize='.$filesize.',fileMime="'.$enc_mimetype.'",ownerId='.$session->id.',uploaderId='.$session->id.',uploaderIP='.$session->ip.',timeUploaded=NOW(),fileType='.$fileType.',categoryId='.$categoryId;
  	$db->query($sql);
  	$fileId = $db->insert_id;
		
		//Identify and handle various types of files
		if (in_array($file_lastname, $this->allowed_image_types)) {
			$this->handleImageUpload($fileId, $FileData);
		} else if (in_array($file_lastname, $this->allowed_audio_types)) {
			$this->handleAudioUpload($fileId, $FileData);
		} else {
			unlink($FileData['tmp_name']);
			return 'Unsupported filetype';
		}
	}

	private function handleAudioUpload($fileId, $FileData)
	{
		global $db;

		//Move the uploaded file to upload directory
		$uploadfile = $this->upload_dir.$fileId;
		if (move_uploaded_file($FileData['tmp_name'], $uploadfile)) return $fileId;
		$db->log('Failed to move file from '.$FileData['tmp_name'].' to '.$uploadfile);
	}

	/* Handle image upload, used internally only */
	private function handleImageUpload($fileId, $FileData)
	{
		global $db;

		list($img_width, $img_height) = getimagesize($FileData['tmp_name']);

		//Resize the image if it is too big, overwrite the uploaded file
		if (($img_width > $this->image_max_width) || ($img_height > $this->image_max_height))
		{
			$resizedFile = $FileData['tmp_name'];
			$resizedImage = $this->resizeImage($FileData['tmp_name'], $resizedFile, $this->image_max_width, $this->image_max_height);
		}

		//create default sized thumbnail
		$thumb_filename = $this->thumbs_dir.$fileId.'_'.$this->thumb_default_width.'x'.$this->thumb_default_height;
		$this->resizeImage($FileData['tmp_name'], $thumb_filename, $this->thumb_default_width, $this->thumb_default_height);

		//Move the uploaded file to upload directory
		$uploadfile = $this->upload_dir.$fileId;
		if (move_uploaded_file($FileData['tmp_name'], $uploadfile)) return $fileId;
		$db->log('Failed to move file from '.$FileData['tmp_name'].' to '.$uploadfile);
	}

	/* Returns array(width, height) resized to maximum $to_width and $to_height while keeping aspect ratio */
	private function resizeImageCalc($filename, $to_width, $to_height)
	{
		list($orig_width, $orig_height) = getimagesize($filename);

		$max_width = $this->image_max_width;
		$max_height = $this->image_max_height;

		if ($to_width && ($to_width < $max_width)) $max_width = $to_width;
		if ($to_height && ($to_height < $max_height)) $max_height = $to_height;

		//Proportionally resize the image to the max sizes specified above
		$x_ratio = $max_width / $orig_width;
		$y_ratio = $max_height / $orig_height;

		if (($orig_width <= $max_width) && ($orig_height <= $max_height))
		{
			return Array($orig_width, $orig_height);
		}
		elseif (($x_ratio * $orig_height) < $max_height)
		{
			return Array($max_width, ceil($x_ratio * $orig_height));
		}

		return Array(ceil($y_ratio * $orig_width), $max_height);
	}

	private function resizeImage($in_filename, $out_filename, $to_width=0, $to_height=0)
	{
		if (empty($to_width) && empty($to_height)) return false;

		$data = getimagesize($in_filename);	//todo, kan man anv�nda list() ?
		$orig_width = $data[0];
		$orig_height = $data[1];
		$mime_type = $data['mime'];
		if (!$orig_width || !$orig_height) return false;

		//Calculate the real width & height to resize too (within $to_width & $to_height), while keeping aspect ratio
		list($tn_width, $tn_height) = $this->resizeImageCalc($in_filename, $to_width, $to_height);

		//echo 'Resizing from '.$orig_width.'x'.$orig_height.' to '.$tn_width.'x'.$tn_height.'<br/>';

		switch ($mime_type)
		{
   		case 'image/png':	$image = imagecreatefrompng($in_filename); break;
   		case 'image/jpeg': $image = imagecreatefromjpeg($in_filename); break;
   		case 'image/gif': $image = imagecreatefromgif($in_filename); break;
   		default: die('Unsupported image type '.$mime_type);
		}

		$image_p = imagecreatetruecolor($tn_width, $tn_height);

		if ($this->resample_resized) {
			imagecopyresampled($image_p, $image, 0,0,0,0, $tn_width, $tn_height, $orig_width, $orig_height);
		} else {
			imagecopyresized($image_p, $image, 0,0,0,0, $tn_width, $tn_height, $orig_width, $orig_height);
		}

		switch ($mime_type)
		{
   		case 'image/png':	imagepng($image_p, $out_filename); break;
   		case 'image/jpeg': imagejpeg($image_p, $out_filename, $this->image_jpeg_quality); break;
   		case 'image/gif': imagegif($image_p, $out_filename); break;
   		default: die('Unsupported image type '.$mime_type);
		}

		imagedestroy($image);
	}

	//These headers allows the browser to cache the output for 30 days. Works with MSIE6 and Firefox 1.5
	private function setCachedHeaders()
	{
		header('Expires: ' . date("D, j M Y H:i:s", time() + (86400 * 30)) . ' UTC');
		header('Cache-Control: Public');
		header('Pragma: Public');
	}


	//Note: These header commands have been verified to work with IE6 and Firefox 1.5 only, no other browsers have been tested
	function sendFile($_id, $download)
	{
		if (!is_numeric($_id)) return false;

		global $db;

		$data = $db->getOneRow('SELECT * FROM tblFiles WHERE fileId='.$_id);
		if (!$data) die;

		list($file_firstname, $file_lastname) = explode('.', strtolower($data['fileName']));

		/* This sends files without extension etc as plain text if you didnt specify to download them */
		if (!$download && (($data['fileMime'] == 'application/octet-stream') || !$file_lastname)) {
			header('Content-Type: text/plain');
		} else {
			header('Content-Type: '.$data['fileMime']);
		}

		if ($download) {
			/* Prompts the user to save the file */
			header('Content-Disposition: attachment; filename="'.basename($data['fileName']).'"');
		} else {
			/* Displays the file in the browser, and assigns a filename for the browser's "save as..." features */
			header('Content-Disposition: inline; filename="'.basename($data['fileName']).'"');
		}

		header('Content-Transfer-Encoding: binary');

		//Serves the file differently depending on what kind of file it is
		if (in_array($file_lastname, $this->allowed_image_types)) {
			//Generate resized image if needed
			$this->sendImage($_id);
		} else {
			$this->setCachedHeaders();

			//Just delivers the file as-is
			header('Content-Length: '. $data['fileSize']);
			echo file_get_contents($this->upload_dir.$_id);
		}

		//Count the file downloads
		if ($this->count_file_views) {
			$db->query('UPDATE tblFiles SET cnt=cnt+1 WHERE fileId='.$_id);
		}
	}
	
	private function sendImage($_id)
	{
		global $session;

		$filename = $this->upload_dir.$_id;
		list($img_width, $img_height) = getimagesize($filename);

		$width = 0;
		if (!empty($_GET['w']) && is_numeric($_GET['w'])) $width = $_GET['w'];
		if (($width < 10) || ($width > 1500)) $width = 0;

		$height = 0;
		if (!empty($_GET['h']) && is_numeric($_GET['h'])) $height = $_GET['h'];
		if (($height < 10) || ($height > 1500)) $height = 0;

		if ($width && (($width < $img_width) || ($height < $img_height)) )  {
			/* Look for cached thumbnail */

			$out_filename = $this->thumbs_dir.$_id.'_'.$width.'x'.$height;

			if (!file_exists($out_filename)) {
				//Thumbnail of this size dont exist, create one
				$this->resizeImage($filename, $out_filename, $width, $height);
			}
		} else {
			$out_filename = $filename;
		}

		if (filemtime($out_filename) < $session->started) {
			$this->setCachedHeaders();
		}

		header('Content-Length: '.filesize($out_filename));
		echo file_get_contents($out_filename);
	}
	
	function getFiles($ownerId, $fileType = 0)
	{
		global $db;

		if (!is_numeric($ownerId) || !is_numeric($fileType)) return array();

		$sql = 'SELECT t1.*,t2.userName AS uploaderName FROM tblFiles AS t1 ';
		$sql .= 'INNER JOIN tblUsers AS t2 ON (t1.uploaderId=t2.userId) ';
		$sql .= 'WHERE t1.ownerId='.$ownerId;
		if ($fileType) $sql .= ' AND t1.fileType='.$fileType;
		$sql .= ' ORDER BY t1.timeUploaded ASC';
		
		return $db->getArray($sql);
	}

	function getFilesByCategory($fileType, $categoryId)
	{
		global $db;

		if (!is_numeric($categoryId) || !is_numeric($fileType)) return array();

		$sql = 'SELECT t1.*,t2.userName AS uploaderName FROM tblFiles AS t1 ';
		$sql .= 'INNER JOIN tblUsers AS t2 ON (t1.uploaderId=t2.userId) ';
		$sql .= 'WHERE t1.categoryId='.$categoryId;
		if ($fileType) $sql .= ' AND t1.fileType='.$fileType;
		$sql .= ' ORDER BY t1.timeUploaded ASC';
		
		return $db->getArray($sql);
	}

	/* Returns a string like "2 KiB" */
	function formatFileSize($bytes)
	{
		//$units = array('bytes', 'KiB', 'MiB', 'GiB', 'TiB');
		$units = array('bytes', 'k', 'mb', 'gb', 'tb');
		foreach ($units as $unit) {
			if ($bytes < 1024) break;
			$bytes = round($bytes/1024, 1);
		}
		return $bytes.' '.$unit;
	}
	
	/* Anv�nds av ajax filen /core/ajax_fileinfo.php f�r att visa fil-detaljer f�r den fil som �r inzoomad just nu*/
	function getFileInfo($_id)
	{
		if (!is_numeric($_id)) return '';

		global $db;

		$q = 'SELECT t1.*,t2.userName AS uploaderName FROM tblFiles AS t1 '.
					'INNER JOIN tblUsers AS t2 ON (t1.uploaderId=t2.userId) '.
					'WHERE t1.fileId='.$_id;

		$file = $db->getOneRow($q);
		if (!$file) return '';

		$result = 'Name: '.htmlentities($file['fileName']).'<br/>'.
							'Filesize: '.$this->formatFileSize($file['fileSize']).'<br/>'.
							'Uploader: '.htmlentities($file['uploaderName']).'<br/>'.
							'At: '.$file['timeUploaded'].'<br/>';
		if ($this->count_file_views) $result .= 'Downloaded: '.$file['cnt'].' times';

		return $result;
	}
}