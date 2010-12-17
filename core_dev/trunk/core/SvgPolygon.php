<?php
/**
 * $Id$
 *
 * @author Martin Lindhe, 2008-2010 <martin@startwars.org>
 */

//STATUS: wip

class SvgPolygon implements ISvgComponent
{
    var $color;             ///< XXXX fill color RGBA
    var $border;            ///< XXXXX border color RGBA
    var $coords = array();  ///< array of x,y coordinates

    function addPoint($x, $y)
    {
        $this->coords[] = array($x, $y);
    }

    function render()
    {
/*
        $fill_a = ($poly['color'] >> 24) & 0xFF;
        $fill_a = round($fill_a/127, 2);        //XXX loss of precision
        if (!$fill_a) $fill_a = 1;    //set missing alpha as 100% alpha
        $poly['color'] = $poly['color'] & 0xFFFFFF;

        if ($poly['border'] !== false) {
            $stroke_a = ($poly['border'] >> 24) & 0xFF;
            $stroke_a = round($stroke_a/127, 2);
            if (!$stroke_a) $stroke_a = 1;
            $poly['border'] = $poly['border'] & 0xFFFFFF;
        }
*/

        $res = '<polygon fill="#eeaa99" fill-opacity="4" stroke-width="1" stroke="#88aa11" stroke-opacity="4"';
/*
            ($fill_a < 1 ? ' fill-opacity="'.$fill_a.'"' : '');
            if ($poly['border'] !== false) {
                $res .=
                ' stroke-width="1" stroke="#'.sprintf('%06x', $poly['border']).'"'.
                ($stroke_a < 1 ? ' stroke-opacity="'.$stroke_a.'"': '');
            }
*/
        $res .= ' points="';
        for ($i=0; $i<count($this->coords); $i++) {
            $res .= $this->coords[$i][0].','.$this->coords[$i][1];
            if ($i < count($this->coords)-1) $res .= ',';
        }
        $res .= '"/>';

        return $res;
    }

}

?>
