<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require_once('time.php');

if (elapsed_seconds(30) != '30 seconds')                  echo "FAIL 1\n";
if (elapsed_seconds(60 * 30) != '30 minutes')             echo "FAIL 2\n";
if (elapsed_seconds(60 * 90) != '1.5 hours')              echo "FAIL 3\n";
if (elapsed_seconds(60 * 60 * 24 * 2) != '2 days')        echo "FAIL 4\n";
if (elapsed_seconds(60 * 60 * 24 * 14) != '2 weeks')      echo "FAIL 5\n";
if (elapsed_seconds(60 * 60 * 24 * 30 * 3) != '3 months') echo "FAIL 6\n";
if (elapsed_seconds(60 * 60 * 24 * 365 * 2) != '2 years') echo "FAIL 7\n";

?>