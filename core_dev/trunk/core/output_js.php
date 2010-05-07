<?php
/**
 * $Id$
 *
 * Helper functions for JavaScript generation
 *
  * @author Martin Lindhe, 2007-2010 <martin@startwars.org>
 */

/**
 * used internally by jsArray1D, jsArray2D
 */
function jsArrayFlat($list, $with_keys)
{
    $all = array();
    foreach ($list as $key => $val)
    {
        $res = ($with_keys ? $key.':' : '');
        if (is_bool($val)) $res .= ($val ? '1' : '0');
        else if (is_numeric($val)) $res .= $val;
        else {
            $val = str_replace('"', '&quot;', $val); //cannot contain "
            $res .= '"'.$val.'"';
        }
        $all[] = $res;
    }
    return implode(',', $all);
}

/**
 * @param $list    array(key1=>val1, key2=>val2)
 * @return ["val1","val2",]   or  [key1:"val1",key2:"val2",]
 */
function jsArray1D($list, $with_keys = true)
{
    return '{'.jsArrayFlat($list, $with_keys).'}';
}

/**
 * Generates Javascript arrays
 * @param $list 2d array
 */
function jsArray2D($list)
{
    $res = '[';

    foreach ($list as $l)
        $res .= jsArray1D($l, true).',';

    $res .= ']';

    return $res;
}

/**
 * @param $ms reload time in milliseconds (1/1000th second)
 */
function js_reload($ms)
{
    if (!is_numeric($ms)) return false;

    $res =
    '<script type="text/javascript">'.
    'setTimeout("location.reload();", '.$ms.');'.
    '</script>';

    return $res;
}

/**
 * Redirects the user to a different page
 */
function js_redirect($url)
{
    if (headers_sent()) {
        die(
        '<script type="text/javascript">'.
        'document.location.href="'.$url.'";'.
        '</script>');
    } else {
        header('Location: '.$url);
        die;
    }
}

/**
 * Renders a date in Javascript format (american): MM/DD/YYYY
 */
function js_date($ts)
{
    return date('m/d/Y', $ts);
}

?>
