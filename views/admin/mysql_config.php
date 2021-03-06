<?php
/**
 * Shows information about current MySQL connection
 */

//TODO: parse db privileges and complain if too low or too high priviliegies!
//TODO: present data in pretty tables
//TODO: use pie charts to show percentage of used memory etc

namespace cd;

$session->requireSuperAdmin();

echo '<h1>MySQL information</h1>';
echo 'Driver: <b>'.get_class($db).'</b><br/>';
echo 'Server version: <b>'.$db->db_handle->server_info.'</b><br/>';
echo 'Client version: <b>'.$db->db_handle->client_info.'</b><br/>';
echo 'Host: <b>'.$db->host.':'.$db->port.'</b><br/>';
echo 'Username: <b>'.$db->username.'</b><br/>';
// echo 'Password: '.($db->password ? $db->password : '(blank)').'<br/>';
echo 'Database: <b>'.$db->database.'</b><br/>';
echo 'Configured charset: <b>'.$db->charset.'</b><br/>';

echo 'Connection charset: <b>'.$db->db_handle->character_set_name().'</b><br/>';
echo 'Host info: <b>'.$db->db_handle->host_info.'</b><br/>';

echo '<br/>';


$q = 'SHOW GRANTS FOR CURRENT_USER';
$priv = Sql::pSelect($q);

// ex:  GRANT USAGE ON *.* TO 'savak'@'%' IDENTIFIED BY PASSWORD '*0...
// ex:  GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER ON *.* TO 'root'@'%' IDENTIFIED BY PASSWORD '*xxx'
// ex:  GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY PASSWORD '*xxx' WITH GRANT OPTION

/// XXXXX FIXME: censor password from string instead of cut it!!!!
//d($priv);
echo '<h2>Privileges</h2>';
foreach ($priv as $p)
{
    $key = key($p);
    $val = current($p);

    echo $key.': <b>'.substr($val, 0, 60).'</b>...<br/>';
}
echo'<br/>';



echo '<h2>Time</h2>';
$db_time = Sql::pSelectItem('SELECT NOW()');
echo 'Database time: '.$db_time.'<br/>';
echo 'Webserver time: '.now().'<br/>';

$uptime = Sql::pSelectRow('SHOW STATUS WHERE Variable_name = ?', 's', 'Uptime');
echo 'Database uptime: <b>'.elapsed_seconds($uptime['Value']).'</b><br/>';

echo '<br/>';


echo '<h2>Character sets</h2>';

$charsets = Sql::pSelectMapped('SHOW VARIABLES LIKE "%character_set%"');
foreach ($charsets as $ch_name => $val) {
    echo $ch_name.' = ';
    if (!in_array($val, array('utf8')))
        echo '<font color="red">'.$val.'</font>';
    else
        echo $val;
    echo '<br/>';
}

$collations = Sql::pSelectMapped('SHOW VARIABLES LIKE "%collation%"');
foreach ($collations as $ch_name => $val) {
    echo $ch_name.' = '.$val.'<br/>';
}
echo '<br/>';

// show MySQL query cache settings

$data = Sql::pSelectMapped('SHOW VARIABLES LIKE "%query_cache%"');

if ($data['have_query_cache'] == 'YES')
{
    echo '<h2>Query cache settings</h2>';
    echo 'Type: '. $data['query_cache_type'].'<br/>';        //valid values: ON, OFF or DEMAND
    echo 'Size: '. formatDataSize($data['query_cache_size']).' (total size)<br/>';
    echo 'Limit: '. formatDataSize($data['query_cache_limit']).' (per query)<br/>';
    echo 'Min result unit: '. formatDataSize($data['query_cache_min_res_unit']).'<br/>';
    echo 'Wlock invalidate: '. $data['query_cache_wlock_invalidate'].'<br/><br/>';

    // current query cache status
    $data = Sql::pSelectMapped('SHOW STATUS LIKE "%Qcache%"');

    echo '<h2>Query cache status</h2>';
    echo 'Hits: '. formatNumber($data['Qcache_hits']).'<br/>';
    echo 'Inserts: '. formatNumber($data['Qcache_inserts']).'<br/>';
    echo 'Queries in cache: '. formatNumber($data['Qcache_queries_in_cache']).'<br/>';
    echo 'Total blocks: '. formatNumber($data['Qcache_total_blocks']).'<br/>';
    echo '<br/>';
    echo 'Not cached: '. formatNumber($data['Qcache_not_cached']).'<br/>';
    echo 'Free memory: '. formatDataSize($data['Qcache_free_memory']).'<br/>';
    echo '<br/>';
    echo 'Free blocks: '. formatNumber($data['Qcache_free_blocks']).'<br/>';
    echo 'Lowmem prunes: '. formatNumber($data['Qcache_lowmem_prunes']);
}
else
{
    echo '<h2>MySQL query cache is disabled</h2>';
}

?>
