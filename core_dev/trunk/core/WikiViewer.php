<?php
/**
 * $Id$
 *
 * @author Martin Lindhe, 2007-2011 <martin@startwars.org>
 */

namespace cd;

require_once('Wiki.php');

class WikiViewer extends Wiki
{
    function render()
    {
        return $this->formatWiki();
    }

}
