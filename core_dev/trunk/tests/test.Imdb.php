<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require_once('Imdb.php');

if (!Imdb::isValidId('tt0499549')) echo "FAIL 1\n";

?>