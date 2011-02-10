<?php
/**
 * $Id$
 *
 * Last.fm API client for querying last.fm for music meta data
 *
 * http://www.last.fm/api/intro
 *
 * @author Martin Lindhe, 2010-2011 <martin@startwars.org>
 */

//STATUS: wip

//TODO: getArtistInfo() parse "similar" artists tag

require_once('HttpClient.php');
require_once('MediaResource.php');

class LastFmClient
{
    static $_instance; ///< singleton

    protected $api_key = 'b25b959554ed76058ac220b7b2e0a026'; // from last.fm api doc

    private function __construct() { }
    private function __clone() {}      //singleton: prevent cloning of class

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self))
            self::$_instance = new self();

        return self::$_instance;
    }

    function setApiKey($s) { $this->api_key = $s; }

    private function query($method, $params)
    {
        $url = 'http://ws.audioscrobbler.com/2.0/?method='.$method.'&api_key='.$this->api_key;

        $http = new HttpClient($url);
        $http->setCacheTime('12h');

        foreach ($params as $key => $val)
            $http->Url->setParam($key, $val);

//d( $http->getUrl() );

        $data = $http->getBody();
        $x = simplexml_load_string($data);

        $attrs = $x->attributes();
        if ($attrs['status'] == 'failed')
            throw new Exception ('last.fm api error: '.$x->error);

        return $x;
    }

    /**
     * @return info about the artist
     */
    function getArtistInfo($artist)
    {
        $xml = $this->query('artist.getInfo', array('artist' => $artist) );

        $artist = new ArtistResource();
        $artist->name           = strval($xml->artist->name);
        $artist->musicbrainz_id = strval($xml->artist->mbid);
        $artist->summary        = strval($xml->artist->bio->summary);
        $artist->detailed_info  = strval($xml->artist->bio->content);

        foreach ($xml->artist->image as $i) {
            $image = new ImageResource();

            $attr = $i->attributes();

            $image->setUrl( strval($i) );
            $image->type = strval($attr->size);

            $artist->images[] = $image;
        }

        foreach ($xml->artist->tags->tag as $t)
            $artist->tags[] = strval($t->name);

        return $artist;
    }

    static function getBestImage($images, $quality = 'mega')
    {
        $scoring = array(
        'small'      => 1,
        'medium'     => 2,
        'large'      => 3,
        'extralarge' => 4,
        'mega'       => 5,
        );

        if (!array_key_exists($quality, $scoring))
            throw new Exception ('unrecognized quality: '.$quality);

        $score = 0;
        $best_url = '';

        foreach ($images as $i) {
            if ($i->type == $quality)
                return $i->getUrl();

            if (isset($scoring[ $i->type ])) {
                if ($scoring[ $i->type ] > $score) {
                    // echo $name. " = ".$val."\n";
                    $score = $scoring[ $i->type ];
                    $best_url = $i->getUrl();
                }
            } else
                throw new Exception ('unknown image quality '.$i->type );
        }

        return $best_url;
    }

    /**
     */
    function getAlbumCovers($artist, $album)
    {
        $xml = $this->query('album.getInfo', array('artist' => $artist, 'album' => $album) );

        if (isset($xml->error)) // eg: "Album not found"
            return false;

        $images = array();
        foreach ($xml->album->image as $i)
        {
            $attrs = $i->attributes();

            $image = new ImageResource();
            $image->type = strval($attrs['size']);
            $image->setUrl( strval($i) );
            $images[] = $image;
        }

        return $images;
    }

    /**
     * @param $quality force given quality if set
     * @return url to best quality album cover for given album
     */
    function getAlbumCover($artist, $album, $quality = '')
    {
        $images = $this->getAlbumCovers($artist, $album);

        return self::getBestImage($images, $quality);
    }

}

?>
