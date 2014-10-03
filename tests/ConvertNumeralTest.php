<?php

namespace cd;

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require_once('ConvertNumeral.php');

class ConvertNumeralTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $arr = array(
        array('decimal', 'binary',  '11',   1011),
        array('decimal', 'octal',   '44',   54),
        array('octal',   'decimal', '33',   27),
        array('octal',   'decimal', '1234', 668),
        array('binary',  'decimal', '1110', 14),
        array('binary',  'decimal', '1011', 11),
        );

        foreach ($arr as $test)
        {
            $res = ConvertNumeral::convert($test[0], $test[1], $test[2]);
            $this->assertEquals($res, $test[3]);
        }
    }
}