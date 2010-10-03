<?php
/**
 * $Id$
 *
 * Conversion functions between different units of mass
 *
 * References
 * ----------
 * http://en.wikipedia.org/wiki/Conversion_of_units#Mass
 *
 * @author Martin Lindhe, 2009-2010 <martin@startwars.org>
 */

require_once('ConvertBase.php');

class ConvertMass extends ConvertBase
{
    protected $scale = array( ///< unit scale to Gram
    'g'  => 1,            //Gram
    'hg' => 100,          //Hectogram
    'kg' => 1000,         //Kilogram
    't'  => 1000000,      //Tonne
    'oz' => 28.349523125, //Ounce (1/16 lb)
    'lb' => 453.59237,    //Pound
    'st' => 6350.29318,   //Stone (14 lb)
    );

    protected $lookup = array(
    'gram'      => 'g',
    'hecto'     => 'hg',
    'hectogram' => 'hg',
    'kilogram'  => 'kg',
    'kilo'      => 'kg',
    'ton'       => 't',  //"metric tonne"
    'tonne'     => 't',
    'ounce'     => 'oz',    'ounces' => 'oz',
    'pound'     => 'lb',    'pounds' => 'lb',
    'stone'     => 'st',    'stones' => 'st',
    );

    function conv($from, $to, $val)
    {
        $from = $this->getShortcode($from);
        $to   = $this->getShortcode($to);

        if (!$from || !$to)
            return false;

        $res = ($val * $this->scale[$from]) / $this->scale[$to];

        if ($this->precision)
            return round($res, $this->precision);

        return $res;
    }

    function convLiteral($s, $to, $from = 'gram')
    {
        return parent::convLiteral($s, $to, $from);
    }

}

?>