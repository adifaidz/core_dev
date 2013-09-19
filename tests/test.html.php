<?php

namespace cd;

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require_once('html.php');

if (htmlchars_decode('ja&nbsp;ha') != 'ja ha')        echo "FAIL 1\n"; // space char is a special NBSP character
if (htmlchars_decode('reg&reg;me') != 'reg®me')       echo "FAIL 2\n";
if (htmlchars_decode('&#039;') != "'")                echo "FAIL 3\n";


if (is_html_color('#fft0ff'))                         echo "FAIL 10\n";
if (is_html_color('#ff0ff'))                          echo "FAIL 11\n";
if (is_html_color('#ffff'))                           echo "FAIL 12\n";
if (is_html_color('#ff'))                             echo "FAIL 13\n";
if (is_html_color('#f'))                              echo "FAIL 14\n";
if (is_html_color('aff'))                             echo "FAIL 15\n";
if (is_html_color(''))                                echo "FAIL 16\n";
if (!is_html_color('#fff'))                           echo "FAIL 17\n";
if (!is_html_color('#FFF'))                           echo "FAIL 18\n";
if (!is_html_color('#ff00ff'))                        echo "FAIL 19\n";
if (!is_html_color('#FF00FF'))                        echo "FAIL 20\n";


if (relurl('/') != '/')                               echo "FAIL 30\n";
if (relurl('?val') != '?val')                         echo "FAIL 31\n";
if (relurl('abp://subscribe') != 'abp://subscribe')   echo "FAIL 32\n";


$s =
'hello '.
'<style>.box h1 {text-align:left;}</style>'.
'<style type="text/css">.xxx</style>'.
'<script>strip me</script>'.
'<script language="text/javascript">strip me</script>'.
'world';
if (strip_html($s) != 'hello world')                  echo "FAIL 40\n";

if (strip_html('hi<!--comment--> bye') != 'hi bye')   echo "FAIL 41\n";

$s = '<!--[if gte mso 9]><x>val</x><![endif]--> res <br/>';
if (strip_html($s) != ' res ')                        echo "FAIL 42\n";

$s = '<!--[if gte mso 9]><w>0</w><![endif]
-->';
if (strip_html($s) != '')                             echo "FAIL 43\n";

$s = '<!-- c1 -->SHOULD_SHOW<!-- c2 -->ALWAYS_SHOWS';
if (strip_html($s) != 'SHOULD_SHOWALWAYS_SHOWS')      echo "FAIL 44\n";

?>