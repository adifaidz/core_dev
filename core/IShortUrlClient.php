<?php
/**
 * @author Martin Lindhe, 2009-2014 <martin@ubique.se>
 */

//STATUS: wip

namespace cd;

require_once('ShortUrlClientBitLy.php');
require_once('ShortUrlClientGooGl.php');
require_once('ShortUrlClientIsGd.php');
require_once('ShortUrlClientTinyUrl.php');

interface IShortUrlClient
{
    /**
     * Creates a short URL from input URL
     *
     * @param $input_url input URL
     * @return short URL or false on error
     */
    static function shorten($input_url);
}
