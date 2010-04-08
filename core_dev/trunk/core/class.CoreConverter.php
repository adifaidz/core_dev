<?php
/**
 * $Id$
 *
 * @author Martin Lindhe, 2009-2010 <martin@startwars.org>
 */

//STATUS: ok

require_once('class.CoreBase.php');

abstract class CoreConverter extends CoreBase
{
	protected $precision = 0;   ///< if set, specifies rounding precision. if unset, return exact result

	function setPrecision($n) { $this->precision = $n; }
}

?>
