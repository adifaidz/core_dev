<?php

namespace cd;

class ConvertCurrencyTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $val = 100;
        $in_sek = ConvertCurrency::convert('USD', 'SEK', $val);

        $this->assertGreaterThan($val, $in_sek); // XXX this may not forever hold true :-P

        // TODO how to properly test this class

        // echo $val." USD is currently worth ".$in_sek." SEK\n";
    }
}
