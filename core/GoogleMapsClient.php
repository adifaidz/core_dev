<?php
/**
 * $Id$
 *
 * Official documentation:
 * http://code.google.com/apis/maps/
 *
 * Google Maps API wiki:
 * http://mapki.com/
 *
 * Google Reverse Geocoding API documentation:
 * http://code.google.com/apis/maps/documentation/services.html#ReverseGeocoding
 *
 * Google Geocoding HTTP API documentation:
 * http://code.google.com/apis/maps/documentation/services.html#Geocoding
 * http://code.google.com/apis/maps/documentation/geocoding/index.html#GeocodingResponses
 *
 * Google Static Maps HTTP API documentation:
 * http://code.google.com/apis/maps/documentation/staticmaps/
 *
 * @author Martin Lindhe, 2008-2012 <martin@ubique.se>
 */

//STATUS: wip

//XXX: google dont return timezone of geolocation queries

namespace cd;

require_once('JSON.php');
require_once('TempStore.php');

class GeoCodeResult  //XXXX MERGE WITH GeoLookupResult into GeoResult !
{
    var $country;     ///< 2 letter country code
    var $name;        ///< eg. "Motala", "Sweden"
    var $latitude;
    var $longitude;
    var $accuracy;
}

class GoogleMapsClient
{
    static function reverse($latitude, $longitude)
    {
        $temp = TempStore::getInstance();
        $key = 'googlemaps/reverse//'.$latitude.'/'.$longitude;

        $data = $temp->get($key);
        if ($data)
            return unserialize($data);

        $url =
        'http://maps.google.com/maps/geo'.
        '?ll='.$latitude.','.$longitude.
        '&output=json'; //XXX "output=xml" returns prettified street address & more info if needed

        $json = JSON::decode($url);
//d($json);
        if ($json->Status->code != 200)
            return false;

        $item = $json->Placemark[0];

        $res = new GeoLookupResult();
        $res->accuracy     = $item->AddressDetails->Accuracy;
        $res->description  = $item->address;
        $res->country_code = $item->AddressDetails->Country->CountryNameCode;
        $res->country_name = $item->AddressDetails->Country->CountryName;

//XXX not returned from google lookup:
//        $res->timezone     = strval($xml->timezone->timezoneId);
//        $res->sunrise      = strval($xml->timezone->sunrise);
//        $res->sunset       = strval($xml->timezone->sunset);

        $temp->set($key, serialize($res));
        return $res;
    }

    /**
     * Performs a Geocoding lookup from street address to coordinates
     *
     * @param $address address to get coordinates for
     * @return coordinates & accuracy of specified location or false
     */
    static function geocode($address)
    {
        $temp = TempStore::getInstance();
        $key = 'googlemaps/geocode//'.$address;

        $data = $temp->get($key);
        if ($data)
            return unserialize($data);

        $url =
        'http://maps.google.com/maps/geo'.
        '?q='.urlencode(trim($address)).
        '&output=json';    //XXX "output=xml" returns prettified street address & more info if needed

        $json = JSON::decode($url);
//d($json);

        if ($json->Status->code != 200)
            return false;

        $item = $json->Placemark[0];

        $res = new GeoCodeResult();
        $res->accuracy  = $item->AddressDetails->Accuracy; // 0 (worst) to 9 (best)

        if       (isset($item->AddressDetails->Country->AdministrativeArea->Locality->LocalityName))
            $res->name = $item->AddressDetails->Country->AdministrativeArea->Locality->LocalityName;
        else if (isset($item->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->LocalityName))
            $res->name = $item->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->LocalityName;
        else if (isset($item->AddressDetails->Country->SubAdministrativeArea->Locality->LocalityName))
            $res->name = $item->AddressDetails->Country->SubAdministrativeArea->Locality->LocalityName;
        else if (isset($item->AddressDetails->Country->CountryName))
            $res->name = $item->AddressDetails->Country->CountryName;
        else if (isset($item->address))
            $res->name = $item->address;
        else {
            d($json);
            throw new \Exception ('ERRORZZZ');
        }

        if (isset($item->AddressDetails->Country->CountryNameCode))
            $res->country   = $item->AddressDetails->Country->CountryNameCode;

        $res->latitude  = $item->Point->coordinates[1];
        $res->longitude = $item->Point->coordinates[0];

        $temp->set($key, serialize($res), '1h');
        return $res;
    }

    /**
     * Creates a link to a static map as a image resource
     *
     * @param $lat latitude (-90.0 to 90.0) horizontal
     * @param $long longitude (-180.0 to 180.0) vertical
     * @param $width up to 640 pixels
     * @param $height up to 640 pixels
     * @param $zoom 0 (whole world) to 21 (showing buildings)
     * @param $maptype mobile, satellite, terrain, hybrid
     * @param $format png8, png32, jpg, jpg-baseline or gif
     * @return URL to static map or false
     */
    static function staticMap($lat, $long, $markers = array(), $path = array(), $width = 512, $height = 512, $zoom = 14, $maptype = 'mobile', $format = 'png8')
    {
        if (!is_numeric($lat) || !is_numeric($long) || !is_numeric($width) || !is_numeric($height))
            throw new \Exception ('bad input');

        if ($lat < -90.0 || $lat > 90.0 || $long < -180.0 || $long > 180.0)
            throw new \Exception ('odd coords');

        if ($width < 0 || $width > 640 || $height < 0 || $height > 640)
            throw new \Exception ('odd sizes');

        if ($zoom < 0 || $zoom > 21)
            throw new \Exception ('odd zoom');

        $url =
        'http://maps.googleapis.com/maps/api/staticmap'.
        '?center='.$lat.','.$long.
        ($zoom == 'auto' ? '' : '&zoom='.$zoom).
        '&size='.$width.'x'.$height.
        '&format='.urlencode($format).
        '&maptype='.urlencode($maptype).
        '&sensor=false';

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

}

?>
