<?php

namespace cd;

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require_once('ConvertTemperature.php');

$arr = array(
array('C', 'F',      300,     572),
array('C', 'K',      300,     573.15),
array('C', 'R',      300,     1031.67),
array('F', 'C',      500,     260),
array('F', 'K',      500,     533.15),
array('F', 'R',      500,     959.67),
array('K', 'C',      0,      -273.15),
array('K', 'F',      0,      -459.67),           // XXX fails, why?
array('K', 'R',      0,       0),
array('R', 'C',      509.67,  10),
array('R', 'F',      509.67,  50),
array('R', 'K',      509.67,  283.15),
array('C', 'kelvin', 100,     373.15),
);

foreach ($arr as $test)
{
    $res = ConvertTemperature::convert($test[0], $test[1], $test[2]);
    if ($res != $test[3]) {
        echo 'FAIL for '.$test[2].' '.$test[0].' => '.$test[1].', got '.$res.', expected '.$test[3]."\n";
    }
}

