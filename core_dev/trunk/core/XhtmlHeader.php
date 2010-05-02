<?php
/**
 * $Id$
 *
 * Generates a XHTML compilant header
 *
 * @author Martin Lindhe, 2009-2010 <martin@startwars.org>
 */

//STATUS: wip

//TODO: rewrite "include feeds" functionality

//TODO LATER: look at xmlwriter in php 6

require_once('IXMLComponent.php');
require_once('class.CoreBase.php');

class XhtmlHeader extends CoreBase implements IXMLComponent
{
    static $_instance;                  ///< singleton class

    private $title, $favicon;

    private $embed_js      = '';
    private $embed_css     = '';

    private $include_js    = array();
    private $include_css   = array();
    private $include_feeds = array();

    private $meta_keywords = array();
    private $opensearch    = array();

    private $reload_time   = 0;         ///< time after page load to reload the page, in seconds
    private $core_dev_root = '';        ///< web path to core_dev for ajax api calls

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self))
            self::$_instance = new self();

        return self::$_instance;
    }

    public function handlePost($p) {}

    function setCoreDevRoot($s) { $this->core_dev_root = $s; }
    function getCoreDevRoot() { return $this->core_dev_root; }

    function setTitle($t) { $this->title = $t; }
    function setFavicon($uri) { $this->favicon = $uri; }
    function setReloadTime($secs) { $this->reload_time = $secs; }

    function includeFeed($uri) { $this->include_feeds[] = $uri; }
    function includeJs($uri) { $this->include_js[] = $uri; }
    function includeCss($uri) { $this->include_css[] = $uri; }

    /**
     * CSS snippets to be added to XXX
     */
    function addCss($s) { $this->embed_css .= $s; }

    /**
     * JavaScript snippets to be added to the <body onload=""> tag
     */
    function addOnload($js) { $this->onload[] = $js; }

    function addOpenSearch($uri, $name = 'Search box')
    {
        $arr = array('url' => $uri, 'name' => $name);
        $this->opensearch[] = $arr;
    }

    /**
     * Adds META keywords tags
     */
    function addMetaKeyword($w)
    {
        if (is_array($w))
            foreach ($w as $t)
                $this->meta_keywords[] = $t;
        else
            $this->meta_keywords[] = $w;
    }

    /**
     * Creates a complete XHTML header, showing rss feeds if available, etc
     */
    public function render()
    {
        $res =
        '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
        "\n".
        '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">'.
        '<head>';

        $res .= '<style type="text/css">';
        $res .= '@import url('.$this->core_dev_root.'css/core.css);';

        foreach ($this->include_css as $css)
            $res .= '@import url('.$css.');';
        $res .= $this->embed_css;

        $res .= '</style>';

        $res .= '<title>'.$this->title.'</title>';

        $res .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>';

        if ($this->meta_keywords)
            $res .= '<meta name="keywords" content="'.implode(',',$this->meta_keywords).'"/>';

        foreach ($this->include_feeds as $feed) {
            //XXX: clean up feed URI's etc, make it more general
            if (!empty($feed['category']) && is_numeric($feed['category'])) $extra = '?c='.$feed['category'];
            else $extra = '';
            $res .= "\t".'<link rel="alternate" type="application/rss+xml" title="'.$feed['title'].'" href="'.$this->core_dev_root.'api/rss_'.$feed['name'].'.php'.$extra.'"/>'."\n";
        }

        foreach ($this->opensearch as $search)
            $res .= '<link rel="search" type="application/opensearchdescription+xml" href="'.$search['url'].'" title="'.$search['name'].'"/>';

        if ($this->favicon)
            $res .= '<link rel="icon" type="image/png" href="'.$this->favicon.'"/>';

        $res .= '<script type="text/javascript" src="'.$this->core_dev_root.'js/coredev.js"></script>';

        //XXX let classes register needed js,css etc headers somehow

        foreach ($this->include_js as $uri)
            $res .= '<script type="text/javascript" src="'.$uri.'"></script>';

        $res .= '</head>';

        $res .= '<body class="yui-skin-sam"'; // required for YUI
        if ($this->embed_js)
            $res .= ' onload="'.implode('', $this->embed_js).'"';
        $res .= '>';

        if ($this->reload_time)
            $res .= js_reload($this->reload_time * 1000);

        $res .= "\n";
        return $res;
    }

}

?>