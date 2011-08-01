<?php
/**
 * $Id$
 *
 * For special usage with unique tokens (activation, private links)
 *
 * All tokens are 40 byte hex string repserentation of sha1 sums (160 bit)
 * See Settings.php for general key->val storage
 *
 * @author Martin Lindhe, 2010-2011 <martin@startwars.org>
 */

//STATUS: wip

//XXX: dont extend from Settings. make its own class
//CODE CLEANUP: use constants.php TOKEN (7) instead of current (4).. will break databases

require_once('Settings.php');

class Token extends Settings
{
    function getOwner($name, $val)
    {
        $this->type = Settings::TOKEN;
        return parent::getOwner($name, $val);
    }

    function get($name, $default = '')
    {
        $this->type = Settings::TOKEN;
        return parent::get($name, $default);
    }

    /**
     * Creates a new token for specified $name
     * @return newly created token
     */
    function generate($name)
    {
        if (!$this->owner)
            return false;

        $session = SessionHandler::getInstance();

        $this->type = Settings::TOKEN;

        do {
            $val = sha1('pOwplopw' . $session->id . mt_rand() . $session->name . 'LAZER!!');

            if (!$this->getOwner($name, $val))
                break;

        } while (1);

        $this->set($name, $val);

        return $val;
    }

}

?>
