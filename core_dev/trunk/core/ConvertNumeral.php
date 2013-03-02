<?php
/**
 * $Id$
 *
 * Converter between different numeral systems
 *
 * http://en.wikipedia.org/wiki/Radix
 * http://en.wikipedia.org/wiki/Numeral_system
 *
 * @author Martin Lindhe, 2010-2013 <martin@startwars.org>
 */

//STATUS: ok

namespace cd;

require_once('core.php'); // for is_alphanumeric()
require_once('ConvertBase.php');
require_once('ConvertRomanNumber.php');

class ConvertNumeral extends ConvertBase
{
    protected $scale = array( ///< digits in the numeral system
    'bin' => 2,
    'oct' => 8,
    'dec' => 10,
    'hex' => 16,
    );

    protected $lookup = array(
    'binary'      => 'bin',
    'octal'       => 'oct',
    'decimal'     => 'dec',
    'hexadecimal' => 'hex',
    );

    function conv($from, $to, $val)
    {
        if ($from == 'auto' && ConvertRomanNumber::isValid($val)) {
            $roman = new ConvertRomanNumber($val);
            return $roman->getAsInteger();
        }

        if (substr($val, 0, 1) == 'b') {
            $val = substr($val, 1);
            $from = 'bin';
        }

        if (substr($val, 0, 1) == 'o') {
            $val = substr($val, 1);
            $from = 'oct';
        }

        if (substr($val, 0, 1) == 'x') {
            $val = substr($val, 1);
            $from = 'hex';
        }

        if (substr($val, 0, 2) == '0x') {
            $val = substr($val, 2);
            $from = 'hex';
        }

        if ($from == 'auto' && is_numeric($val))
            $from = 'dec';

        if ($from == 'auto' && !is_numeric($val))
            throw new \Exception ('unhandled number conversion of '.$val.' to '.$to);

        $from = $this->getShortcode($from);
        $to   = $this->getShortcode($to);

        if (!$from || !$to)
            return false;

        if (!is_alphanumeric($val))
            return false;

        $base_from = $this->scale[$from];
        $base_to   = $this->scale[$to];

        return base_convert($val, $base_from, $base_to);
    }

    function convLiteral($s, $to, $from = 'decimal')
    {
        return parent::convLiteral($s, $to, $from);
    }

}
