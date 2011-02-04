<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require_once('GeoIp.php');

if ( GeoIp::getCountry('www.ica.se') != 'SE') echo "FAIL 1\n";
if ( GeoIp::getTimezone('www.stockholm.se') != 'Europe/Stockholm') echo "FAIL 2\n";

print_r(  GeoIp::getRecord('www.google.com')  );

echo GeoIp::renderVersion();

?>
