<?php
/**
 * $Id$
 *
 * @author Martin Lindhe, 2007-2010 <martin@startwars.org>
 */

require_once('Wiki.php');

class WikiViewer extends Wiki
{
    function render()
    {
        return $this->formatWiki();
    }

}