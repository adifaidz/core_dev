<?php
/**
 * $Id$
 *
 * Simple newsfeed (RSS, Atom) reader/writer with support for RSS 2.0 and Atom 1.0
 *
 * Output mostly comply with http://feedvalidator.org/
 *
 * @author Martin Lindhe, 2008-2009 <martin@startwars.org>
 */

//STATUS: ok, need more testing

//TODO use Url instead of NewsFeed->url param too
//XXX atom output: no way to embed video duration, <link length="x"> is size of the resource, in bytes.

require_once('class.CoreList.php');
require_once('prop_Duration.php');
require_once('prop_Url.php');
require_once('prop_Timestamp.php');

require_once('client_http.php');

require_once('input_rss.php');
require_once('input_atom.php');

class NewsItem extends CoreBase
{
	var $title;
	var $desc;
	var $author;

	var $guid;
	var $image_mime;
	var $image_url;
	var $video_mime;
	var $video_url;

	var $Duration;  ///< video duration
	var $Timestamp;
	var $Url;       ///< location of news article

	function __construct()
	{
		$this->Duration  = new Duration();
		$this->Timestamp = new Timestamp();
		$this->Url       = new Url();
	}
}

class NewsFeed extends CoreList
{
	private $version   = 'core_dev NewsFeed 1.0';
	private $title     = 'Untitled news feed';
	private $desc;
	private $url       = '';      ///< full url to this feed
	private $ttl       = 15;      ///< time to live, in minutes
	private $headers   = true;    ///< shall we send mime type?

	function getTitle() { return $this->title; }

	function setTitle($n) { $this->title = $n; }
	function setUrl($n) { $this->url = $n; }

	function sendHeaders($bool = true) { $this->headers = $bool; }

	/**
	 * Adds a entry to the feed list
	 */
	function addItem($i)
	{
		switch (get_class($i)) {
		case 'NewsItem':
			$item = $i;
			break;

		case 'MediaItem':
	//	d($i);
			//convert a MediaItem into a NewsItem
			$item = new NewsItem();

			$item->title        = $i->title;
			$item->desc         = $i->desc;
			$item->image_url    = $i->thumbnail;
			$item->image_mime   = file_get_mime_by_suffix($i->thumbnail);

			$item->Url      ->set($i->Url->get() );
			$item->Duration ->set($i->Duration->get() );
			$item->Timestamp->set($i->Timestamp->get() );
			break;

		default:
			d('NewsFeed->addItem cant handle '.get_class($i) );
			return false;
		}
		parent::addItem($item);
	}

	/**
	 * Loads input data from RSS or Atom feeds into NewsItem entries
	 */
	function load($data)
	{
		if (is_url($data)) {
			$http = new HttpClient($data);
			if ($this->debug) $http->setDebug();
			$data = $http->getBody();
		}

		if (strpos($data, '<rss ') !== false) {
			$feed = new input_rss();
		} else if (strpos($data, '<feed ') !== false) {
			$feed = new input_atom();
		} else {
			echo 'NewsFeed->load error: unhandled feed: '.substr($data, 0, 100).' ...'.dln();
			return false;
		}

		if ($this->debug) $feed->setDebug();
		$feed->parse($data);
		$this->title = $feed->getTitle();

		$this->addItems( $feed->getItems() );
	}

	/**
	 * Sorts the list
	 */
	function sort($callback = '')
	{
		if (!$callback) $callback = array($this, 'sortListDesc');

		uasort($this->items, $callback);
	}

	/**
	 * List sort filter
	 * @return Internal list, sorted descending by published date
	 */
	private function sortListDesc($a, $b)
	{
		if (!$a->Timestamp->get()) return 1;

		return ($a->Timestamp->get() > $b->Timestamp->get()) ? -1 : 1;
	}

	/**
	 * Render feed as Atom or RSS
	 */
	function render($format = 'rss2')
	{
		//use executing location if no feed url is specified
		if (!$this->url)
			$this->url = xhtmlGetUrl();

		switch ($format) {
		case 'atom':
			if ($this->headers) header('Content-type: application/atom+xml');
			return $this->renderATOM();

		case 'rss':
		case 'rss2':
			if ($this->headers) header('Content-type: application/rss+xml');
			return $this->renderRSS2();
		}
		return false;
	}

	/**
	 * Renders the feed in Atom 1.0 format
	 * http://www.atomenabled.org/developers/syndication
	 */
	function renderATOM()
	{
		$res =
		'<?xml version="1.0" encoding="UTF-8"?>'.
		'<feed xmlns="http://www.w3.org/2005/Atom">'.
			'<id>'.htmlspecialchars($this->url).'</id>'.
			'<title><![CDATA['.$this->title.']]></title>'.
			//'<updated>'.$this->Timestamp->getRFC3339().'</updated>'.
			'<link rel="self" href="'.htmlspecialchars($this->url).'"/>'.
			'<generator>'.$this->version.'</generator>'."\n";

		foreach ($this->getItems() as $item)
		{
			//link directly to video if no webpage url was found
			if (!$item->Url->get() && $item->video_url)
				$item->Url->set( $item->video_url );

			$res .=
			'<entry>'.
				'<id>'.($item->guid ? $item->guid : $item->Url->get(true) ).'</id>'.
				'<title><![CDATA['.$item->title.']]></title>'.
				'<link rel="alternate" href="'.$item->Url->get(true).'"/>'.
				'<summary><![CDATA['.($item->desc ? $item->desc : ' ').']]></summary>'.
				'<updated>'.$item->Timestamp->getRFC3339().'</updated>'.
				'<author><name>'.$item->author.'</name></author>'.
				($item->video_url ? '<link rel="enclosure" type="'.$item->video_mime.'" href="'.htmlspecialchars($item->video_url).'"/>' : '').
				($item->image_url ? '<link rel="enclosure" type="'.$item->image_mime.'" href="'.htmlspecialchars($item->image_url).'"/>' : '').
			'</entry>'."\n";
		}
		$res .=
		'</feed>';
		return $res;
	}

	/**
	 * Renders the feed in RSS 2.0 format with Media RSS tags for media content
	 *
	 * http://www.rssboard.org/rss-specification
	 * <media> extension: http://video.search.yahoo.com/mrss
	 */
	function renderRSS2()
	{
		$res =
		'<?xml version="1.0" encoding="UTF-8"?>'.
		'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">'.
			'<channel>'.
				'<title><![CDATA['.$this->title.']]></title>'.
				'<link>'.htmlspecialchars($this->url).'</link>'.
				'<description><![CDATA['.$this->desc.']]></description>'.
				($this->ttl ? '<ttl>'.$this->ttl.'</ttl>' : '').
				'<atom:link rel="self" type="application/rss+xml" href="'.htmlspecialchars($this->url).'"/>'.
				'<generator>'.$this->version.'</generator>'."\n";

		foreach ($this->getItems() as $item)
		{
			//link directly to video if no webpage url was found
			if (!$item->Url->get() && $item->video_url)
				$item->Url->set( $item->video_url );

			$res .=
			'<item>'.
				'<title><![CDATA['.$item->title.']]></title>'.
				'<link>'.$item->Url->get(true).'</link>'.
				'<description><![CDATA['.$item->desc.']]></description>'.
				'<pubDate>'.$item->Timestamp->getRFC882().'</pubDate>'.
				($item->guid ? '<guid>'.$item->guid.'</guid>' : '').
				($item->video_url ? '<media:content medium="video" type="'.$item->video_mime.'" url="'.htmlspecialchars($item->video_url).'"'.($item->Duration->get() ? ' duration="'.$item->Duration->inSeconds().'"' : '').'/>' : '').
				($item->image_url ? '<media:content medium="image" type="'.$item->image_mime.'" url="'.htmlspecialchars($item->image_url).'"/>' : '').
			'</item>'."\n";
		}

		$res .=
			'</channel>'.
		'</rss>';

		return $res;
	}

}


?>
