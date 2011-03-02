<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require_once('IconReader.php');

$in = '/devel/web/core_dev/trunk/tests/ICO_files/unsupported-3.ico';

foreach (IconReader::getImages($in) as $idx => $i)
    imagepng($i, basename($in).'-'.$idx.'.png');





?>
