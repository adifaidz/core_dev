<?php
/**
 * $Id$
 *
 * http://en.wikipedia.org/wiki/Atom_(standard)
 *
 * @author Martin Lindhe, 2008-2009 <martin@startwars.org>
 */

class input_atom
{
	private $inside_item = false;
	private $current_tag = '';

	private $link, $title, $desc, $pubDate, $guid;
	private $attrs;
	private $video_url, $video_type, $duration;
	private $image_url, $image_type;
	private $callback = '';

	private $entries = array();

	function __construct($data, $callback = '')
	{
		if (is_url($data)) {
			$u = new http($data);
			$data = $u->fetch();
		}

		$parser = xml_parser_create();
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, 'startElement', 'endElement');
		xml_set_character_data_handler($parser, 'characterData');

		if (function_exists($callback)) $this->callback = $callback;

		if (!xml_parse($parser, $data, true)) {
			echo "parseATOM XML error: ".xml_error_string(xml_get_error_code($parser))." at line ".xml_get_current_line_number($parser)."\n";
		}
		xml_parser_free($parser);
	}

	function getEntries() { return $this->entries; }

	function startElement($parser, $name, $attrs = '')
	{
		$this->current_tag = $name;
		$this->attrs = $attrs;

		if ($this->current_tag == 'ENTRY') {
			$this->inside_item = true;
		}
	}

	function endElement($parser, $tagName, $attrs = '')
	{
		if ($tagName == 'LINK' && $this->inside_item) {
			switch ($this->attrs['REL']) {
			case 'alternate':
				$this->link = $this->attrs['HREF'];
				break;
			case 'enclosure':
				switch ($this->attrs['TYPE']) {
				case 'video/x-flv':
					$this->video_url  = $this->attrs['HREF'];
					$this->video_type = $this->attrs['TYPE'];
					if (!empty($this->attrs['LENGTH'])) $this->duration   = $this->attrs['LENGTH'];
					break;

				case 'image/jpeg':
					$this->image_url  = $this->attrs['HREF'];
					$this->image_type = $this->attrs['TYPE'];
					break;

				default:
					echo "unknown enclosure mimetype: ".$this->attrs['TYPE']."\n";
					die;
					break;
				}
				break;
			case 'replies':
				//FIXME: handle
				break;
			case 'edit': //XXX ???
			case 'self': //XXX ???
				break;
			default:
				echo "unknown link type: ".$this->attrs['REL']."\n";
				die;
			}
		}
		if ($tagName == 'ENTRY') {
			$row['link']     = trim($this->link);
			$row['title']    = html_entity_decode(trim($this->title), ENT_QUOTES, 'UTF-8');
			$row['desc']     = html_entity_decode(trim($this->desc),  ENT_QUOTES, 'UTF-8');
			if ($row['title'] == $row['desc']) $row['desc'] = '';
			$row['pubdate']  = strtotime(trim($this->pubDate));
			$row['guid']     = trim($this->guid);
			//$row['guid2']    = trim($this->guid2); //XXX remove hack!
			$row['duration'] = trim($this->duration);
			if ($this->video_url)  $row['video'] = $this->video_url;
			if ($this->video_type) $row['video_type'] = $this->video_type;
			if ($this->image_url)  $row['image'] = $this->image_url;
			if ($this->video_type) $row['image_type'] = $this->image_type;

			if ($this->callback) call_user_func($this->callback, $row);
			$this->entries[] = $row;

			$this->attrs = '';
			$this->inside_item = false;

			$this->link = '';
			$this->title = '';
			$this->desc = '';
			$this->pubDate = '';
			$this->guid = '';
			$this->video_url  = '';
			$this->video_type = '';
			$this->duration   = 0;
			$this->image_url  = '';
			$this->image_type = '';
		}
	}

	function characterData($parser, $data)
	{
		if (!$this->inside_item) return;
		switch ($this->current_tag) {
		case 'TITLE':
			$this->title .= $data;
			break;

		case 'SUMMARY':
			$this->desc .= $data;
			break;

		case 'UPDATED':
			$this->pubDate .= $data;
			break;

		case 'ID':
			$this->guid .= $data;
			break;
		}
	}

}

?>