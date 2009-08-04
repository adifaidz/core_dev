<?php
require_once('/var/www/core_dev/core/core.php');
require_once('/var/www/core_dev/core/input_http.php');

if (!is_url('http://server.com/file.php')) echo "FAIL 1\n";
if (!is_url('https://server.com/file.php')) echo "FAIL 2\n";
if (!is_url('http://server.com:1000/file.php')) echo "FAIL 3\n";
if (!is_url('http://server.com:80/file.php')) echo "FAIL 4\n";
if (!is_url('http://server.com/')) echo "FAIL 5\n";

if (!is_url('http://server.com/path?arg=value')) echo "FAIL 6\n";
if (!is_url('http://server.com/path?arg=value#anchor')) echo "FAIL 7\n";
if (!is_url('http://server.com/path?arg=value&arg2=4')) echo "FAIL 8\n";
if (!is_url('http://server.com/path?arg=value&amp;arg2=4')) echo "FAIL 9\n";

if (!is_url('http://username@server.com/path?arg=value')) echo "FAIL 10\n";
if (!is_url('http://username:password@server.com/path?arg=value')) echo "FAIL 11\n";

if (is_url('chaos')) echo "FAIL 12\n";
if (is_url('chaos.com')) echo "FAIL 13\n";
if (is_url('http://space in url.com/path.php')) echo "FAIL 14\n";



$url = 'http://www.google.com/';
if (http_status($url) != 302) echo "FAIL 15: ".http_status($url)."\n";

$headers = http_head($url);
echo "\nheaders:\n";
print_r($headers)."\n";

echo "last modified: ".formatTime(http_last_modified($headers))."\n";
echo "content-length: ".http_content_length($headers)."\n";

?>
