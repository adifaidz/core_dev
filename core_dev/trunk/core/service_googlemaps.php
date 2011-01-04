<?php
/**
 * $Id$
 *
 * Functions to interact with the Google Maps API
 *
 * Official documentation:
 * http://code.google.com/apis/maps/
 *
 * Google Maps API wiki:
 * http://mapki.com/
 *
 * @author Martin Lindhe, 2008-2011 <martin@startwars.org>
 */

//STATUS: unused, rewrite

require_once('input_coordinates.php');
require_once('input_csv.php');

//$config['google_maps']['api_key'] = '';

/**
 * Displays a static map
 *
 * Google Static Maps HTTP API documentation:
 * http://code.google.com/apis/maps/documentation/staticmaps/
 *
 * @param $lat latitude (-90.0 to 90.0) horizontal
 * @param $long longitude (-180.0 to 180.0) vertical
 * @param $width up to 640 pixels
 * @param $height up to 640 pixels
 * @param $zoom 0 (whole world) to 19 (very detailed view) or "auto" to autozoom
 * @param $maptype mobile, satellite, terrain, hybrid
 * @param $format png8, png32, jpg, jpg-baseline or gif
 * @return URL to static map or false
 */
function googleMapsStaticMap($lat, $long, $markers = array(), $path = array(), $width = 512, $height = 512, $zoom = 14, $maptype = 'mobile', $format = 'png8')
{
    global $config;
    if (!is_numeric($lat) || !is_numeric($long) || !is_numeric($width) || !is_numeric($height)) return false;
    if ($lat < -90.0 || $lat > 90.0 || $long < -180.0 || $long > 180.0) return false;
    if ($width < 0 || $width > 640 || $height < 0 || $height > 640) return false;
    if ((is_numeric($zoom) && ($zoom < 0 || $zoom > 19)) || is_string($zoom) && $zoom != 'auto') return false;
    if (empty($config['google_maps']['api_key'])) die('google maps api_key not set!');

    $url = 'http://maps.google.com/staticmap'.
        '?center='.$lat.','.$long.
        ($zoom == 'auto' ? '' : '&zoom='.$zoom).
        '&size='.$width.'x'.$height.
        '&format='.urlencode($format).
        '&maptype='.urlencode($maptype).
        '&key='.$config['google_maps']['api_key'];

    $cols = array('red', 'green', 'blue', 'orange', 'purple', 'brown', 'yellow', 'gray', 'black', 'white');

    if (!empty($markers)) {
        $url .= '&markers=';
        for ($i = 0; $i<count($markers); $i++) {
            if ($i == 0) $desc = $cols[$i];
            else $desc = 'mid'.$cols[$i];
            $url .= $markers[$i]['x'].','.$markers[$i]['y'].','.$desc.($i+1);
            if ($i < count($markers)-1) $url .= '|';
        }
    }

    $width = array(6,4,2,2,1,1,1,1,1,1,1,1);

    if (!empty($path)) {
        $alpha = 0xA0;
        for ($i = 0; $i<count($path)-1; $i++) {
            $url .= '&path=rgba:0x0000ff'.dechex($alpha).',weight:'.$width[$i].
                '|'.$path[$i]['x'].','.$path[$i]['y'].
                '|'.$path[$i+1]['x'].','.$path[$i+1]['y'];
            if ($alpha > 0x40) $alpha -= 0x20;
        }
    }

    return $url;
}

/**
 * Performs a Geocoding lookup from street address
 *
 * Google Geocoding HTTP API documentation:
 * http://code.google.com/apis/maps/documentation/services.html#Geocoding
 *
 * @param $address address to get coordinates for
 * @return coordinates & accuracy of specified location or false
 */
function googleMapsGeocode($address)
{
    global $config;

    $url = 'http://maps.google.com/maps/geo'.
        '?q='.urlencode(trim($address)).
        '&output=csv'.    //XXX "xml" output format returns prettified street address & more info if needed
        '&key='.$config['google_maps']['api_key'];

    $res = csvParseRow(file_get_contents($url));
    if ($res[0] != 200 || $res[1] == 0) return false;

    $out['x'] = $res[2];
    $out['y'] = $res[3];
    $out['accuracy'] = $res[1];    //0 (worst) to 9 (best)
    return $out;
}

/**
 * Performs a reverse geocoding lookup from coordinates
 *
 * Google Reverse Geocoding API documentation:
 * http://code.google.com/apis/maps/documentation/services.html#ReverseGeocoding
 *
 * @param $lat Latitude
 * @param $long Longitude
 * @return name & accuracy of specified location or false
 */
function googleMapsReverseGeocode($lat, $long)  //XXX DELETE FUNCTION, use GeoLookupClient instead
{
    global $config;

    $url = 'http://maps.google.com/maps/geo'.
        '?ll='.$lat.','.$long.
        '&output=csv'.    //XXX "xml" output format returns prettified street address & more info if needed
        '&key='.$config['google_maps']['api_key'];

    $res = csvParseRow(file_get_contents($url));
    if ($res[0] != 200 || $res[1] == 0) return false;

    $out['name'] = utf8_encode($res[2]);
    $out['accuracy'] = $res[1];    //0 (worst) to 9 (best)
    return $out;
}

?>
