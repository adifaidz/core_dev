<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require_once('db_mysqli.php');
require_once('Blog.php');

die("FIXME: rewrite test to work on fake db");


$db = new db_mysqli(array('host'=>'localhost', 'username'=>'root', 'password'=>'', 'database'=>'dbIssues'));



$b = new Blog();
echo $b->edit();

?>
