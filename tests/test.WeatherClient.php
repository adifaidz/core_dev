<?php

namespace cd;

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require_once('WeatherClient.php');

$client = new WeatherClient();

/*
$res = $client->getWeatherReport('Norrkoping', 'Sweden');
print_r($res);
*/


$res = $client->getWeatherReport('Stockholm', 'Sweden');
print_r($res);



/*
$res = $client->getCitiesByCountry('sweden');
print_r($res);
*/

?>