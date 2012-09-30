<?php

namespace cd;

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require('Duration.php');

$dur = new Duration(3600 + 46);
if ($dur->render() != '1:00:46') echo "FAIL 1\n";

$dur = new Duration(64800 + 3180 + 19);
if ($dur->render() != '18:53:19') echo "FAIL 2\n";

$dur = new Duration(5278156);
if ($dur->render() != '1466:09:16') echo "FAIL 3\n";

$dur = new Duration('60s');
if ($dur->renderRelative() != '1 minute') echo "FAIL 4\n";

$dur = new Duration('4w');
if ($dur->renderRelative() != '4 weeks') echo "FAIL 5\n";

?>
