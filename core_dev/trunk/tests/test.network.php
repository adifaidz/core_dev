<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require_once('network.php');

$x = IPv4_to_GeoIP('192.168.0.1');
$valid = array(
    '192.168.0.1',
    '80.0.0.0/8',
    '240.0.0.0/8',
);

if ($x != 3232235521) echo "FAIL 1\n";
if (GeoIP_to_IPv4($x) != '192.168.0.1') echo "FAIL 2\n";
if (!match_ip('240.212.11.42', $valid)) echo "FAIL 3\n";
if (match_ip('241.212.11.42', $valid)) echo "FAIL 4\n";



$valid_urls = array(
'http://www.google.se/search?hl=sv&source=hp&q=&btnI=Jag+har+tur&meta=&aq=f&aqi=&aql=&oq=&gs_rfai=',
'https://some-url.com/?query=&name=joe?filter=*.*#some_anchor',
'http://valid-url.without.space.com',
'http://127.0.0.1/test',
'http://hh-1hallo.msn.blabla.com:80800/test/test/test.aspx?dd=dd&id=dki',
'http://web5.uottawa.ca/admingov/reglements-methodes.html',
'ftp://username:password@example.com:21/file.zip',
'http://www.esa.int',
'http://at.activation.com/track/me;1442:PPS35:tta/',
'http://maps.google.com/maps/geo?ll=11.11,11.11&output=json&key=2sddf-d3d3-d3d3d',
'http://url.com/path|path2',
'http://url.net/What\'s%20new%20in%20V4.9a.txt',
);

foreach ($valid_urls as $url)
    if (!is_url($url)) echo "URL FAIL BUT IS VALID ".$url."\n";

$invalid_urls = array(
'http://-invalid.leading-char.com',
'http:// invalid with spaces.com',
'http://invalid.url-with a.space.com',
'http://good-domain.com/bad url with space',
'https://ssl.',   //XXX is detected as valid
);

foreach ($invalid_urls as $url)
    if (is_url($url)) echo "URL SUCCESS BUT IS INVALID ".$url."\n";


$tmp = implode(' glue ', $valid_urls);
$test_matches = match_urls($tmp);


//compare against $valid_urls, should be the same
$err = array_diff($valid_urls, $test_matches);
if (count($err)) {
    echo "ERROR: the following mismatches occured!\n";
    var_dump($err);
}

?>
