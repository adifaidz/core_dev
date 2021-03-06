<?php

namespace cd;

// TODO have a separate test db!
require_once('/home/ml/dev/fmf/snabbsvar/config.php');

require_once('DatabaseMysqlPDO.php');

/*
//  TODO more reliable test
$q = "SELECT * FROM tblUsers";
$users = Sql::pSelect($q);
if (count($users) < 2)    echo "FAIL 1\n";



// TODO more reliable test
$q = 'SELECT * FROM tblUsers WHERE name = ?';
$row = Sql::pSelectRow($q, 's', 'martin');
if (count($row) != 10)    echo "FAIL 2\n";
//d($row);


$q = 'SELECT COUNT(*) FROM tblUsers WHERE name = ?';
$cnt = Sql::pSelectItem($q, 's', 'martin');
if ($cnt != 1)    echo "FAIL 3\n";
//d($row);


$q = 'UPDATE tblUsers SET time_created = ? WHERE name = ?';
$chk = Sql::pUpdate($q, 'ss', '2013-01-01 23:59:58', 'kalle');
if ($chk > 1)   echo "FAIL 4\n";


$q = 'CALL FindUnknownWords(?)';
$x = Sql::pStoredProc($q, 's', "jag är en liten mkorkrö mus");
d($x);


$q = 'REPLACE tblSessionData (session_id,session_data,expires) VALUES(?, ?, ?)';
$xxx = Sql::pUpdate($q, 'sss', 666, "hejhej", sql_datetime( time() ) );
d($xxx);


$q = 'INSERT INTO tblLikes (owner,type) VALUES (?, ?)';
$id = Sql::pInsert($q, 'ii', 4,9);
if ($id < 2)    echo "FAIL 5\n";



$w = new Word();
$w->id = 8;
$w->value = "sten";
if (SqlObject::exists($w, 'oWord') != true)   echo "FAIL 10\n";

$w = new Word();
$w->id = 8;
$w->value = "st2en";
if (SqlObject::exists($w, 'oWord') == true)   echo "FAIL 11\n";

*/

// TODO more reliable test of ListToObjects
$q =
'SELECT * FROM tblBookmarks'.
' WHERE owner = ? AND type = ?';
$list = Sql::pSelect($q, 'ii', 2, 100);
$objs = SqlObject::ListToObjects($list, 'Bookmark');
if (count($objs) < 5)   echo "FAIL 20\n";



$q =
'SELECT * FROM tblBookmarks'.
' WHERE id = ?';
$row = Sql::pSelectRow($q, 'i', 66);
$obj = SqlObject::RowToObject($row, 'Bookmark');
if ($obj->id != 66) echo "FAIL 30\n";


$bb = Bookmark::get(66);
d($bb);

