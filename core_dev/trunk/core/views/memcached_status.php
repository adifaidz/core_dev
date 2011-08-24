<?php
/**
 * Shows detailed information of all connected memcached servers, which is used by the TempStore class
 */

$tempstore_div = 'tss_'.mt_rand();

echo ' | <a href="#" onclick="return toggle_el(\''.$tempstore_div.'\');">tmp</a> ';

$temp = TempStore::getInstance();

$css =
'display:none;'.
'overflow:auto;'.
'padding:4px;'.
'border:#000 1px solid;';

echo '<div id="'.$tempstore_div.'" style="'.$css.'">';

foreach ($temp->getServerStats() as $host => $stat)
{
    echo 'Currently <b>'.$stat['curr_items'].'</b> items, <b>'.$stat['curr_connections'].'</b> connections<br/>';
    echo 'Total <b>'.$stat['total_items'].'</b> items, <b>'.$stat['total_connections'].'</b> connections<br/>';
    echo '<br/>';

    echo 'Cmd: <b>'.$stat['cmd_get'].'</b> get, <b>'.$stat['cmd_set'].'</b> set<br/>';
    echo 'Get: <b>'.$stat['get_hits'].'</b> hits, <b>'.$stat['get_misses'].'</b> misses<br/>';
    echo 'Evictions: <b>'.$stat['evictions'].'</b><br/>';  // ????
    echo 'Threads: <b>'.$stat['threads'].'</b><br/>';
    echo '<br/>';

    echo 'Used memory: <b>'.byte_count($stat['bytes']).'</b>'.
    ' (<b>'.round($stat['bytes'] / $stat['limit_maxbytes'] * 100, 1).'%</b>'.

    ' of <b>'.byte_count($stat['limit_maxbytes']).'</b>)'.
    '<br/>';

    echo 'Bytes read: <b>'.byte_count($stat['bytes_read']).'</b><br/>';
    echo 'Bytes written: <b>'.byte_count($stat['bytes_written']).'</b><br/>';
    echo '<br/>';

    echo 'Server: <b>'.$host.'</b> running <b>memcached '.$stat['version'].'</b><br/>';
    echo 'Server time: <b>'.sql_datetime($stat['time']).'</b><br/>';
    echo 'Uptime: <b>'.elapsed_seconds($stat['uptime']).'</b><br/>';
}

echo '</div>';

?>