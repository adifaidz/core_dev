<?php
/**
 * $Id$
 *
 * IMPORTANT! DONT CHANGE NUMBERS, IT WILL BREAK TEH DATABASES!
 *
 * @author Martin Lindhe, 2010-2012 <martin@startwars.org>
 */

//STATUS: wip

/**
 * GLOBAL TYPE CONSTS
 * defines possible objects and object owners
 *
 * Reserved 1-50. Use a number above 50 for your own types
 */
define('USER',             1);  ///< normal, public userfile
define('NEWS',             2);  ///< news categories
define('CUSTOMER',         3);  ///< ApiCustomer setting
define('WIKI',             4);  ///< category for wiki file attachments, to allow better organization if needed
define('SITE',             5);  ///< for SITE/APP settings etc
define('FILE',             6);  ///< comments for a file
define('TOKEN',            7);  ///< activation tokens etc
define('BLOG',             8);  ///< normal, personal blog category
define('EXTERNAL',         9);  ///< setting type relies on some external component
define('IP',              10);  ///< setting is associated with an IP address, such as a comment (TODO: or a ip block)
define('POLL',            11);  ///< category is owned by a poll
define('USERDATA_OPTION', 20);  ///< used to hold options in tblSettings for UserDataFieldOptions
define('PRIV_MSG',        21);  ///< a private text message

define('RECORDING_PRES',  30);  ///< stream recording of a user presentation
define('RECORDING_MSG',   31);  ///< stream recording of a private message

/**
 * tblCategory.permissions
 */
define('PERM_PUBLIC',  0x01); ///< public category
define('PERM_PRIVATE', 0x02); ///< owner and owner's friends can see the content
define('PERM_HIDDEN',  0x04); ///< only owner can see the content
define('PERM_USER',    0x40); ///< category is created by user
define('PERM_GLOBAL',  0x80); ///< category is globally available to all users


/**
 * session types for different types of user authentication
 * used in tblUsers.type
 */
define('SESSION_REGULAR',  1); ///< internal session handler (default)
define('SESSION_FACEBOOK', 2);

function getSessionTypes()
{
    return array(
    SESSION_REGULAR   => 'Regular',
    SESSION_FACEBOOK  => 'Facebook',
    );
}

/**
 * tblUserGroups.level
 */
define('USERLEVEL_NORMAL',      0);
define('USERLEVEL_WEBMASTER',   1);
define('USERLEVEL_ADMIN',       2);
define('USERLEVEL_SUPERADMIN',  3);

function getUserLevels()
{
    return array(
    USERLEVEL_NORMAL     => 'Normal',
    USERLEVEL_WEBMASTER  => 'Webmaster',
    USERLEVEL_ADMIN      => 'Admin',
    USERLEVEL_SUPERADMIN => 'Super Admin',
    );
}

function getUserLevelName($n)
{
    $x = getUserLevels();
    return $x[ $n ];
}

// ISO 639-2 naming (language names): http://en.wikipedia.org/wiki/List_of_ISO_639-2_codes
define('LANG_SWE', 1);
define('LANG_ENG', 2);

function getLanguages()
{
    return array(
    LANG_SWE => 'Swedish',
    LANG_ENG => 'English',
    );
}

function language($n)
{
    $x = getLanguages();
    return $x[ $n ];
}

// ISO 3166-1 alpha-3 naming (country names): http://en.wikipedia.org/wiki/ISO_3166-1_alpha-3
define('COUNTRY_SWE', 1); // Sweden
define('COUNTRY_GBR', 2); // United Kingdoms

function getCountries()
{
    return array(
    COUNTRY_SWE => 'SWE',
    COUNTRY_GBR => 'GBR',
    );
}

function getCountryCode($n)
{
    $x = getCountries();
    return $x[ $n ];
}

/**
 * Translates
 * from "ISO 3166-1 alpha-2"
 * to   "ISO 3166-1 alpha-3"
 */
function country_2_to_3_letters($s)
{
    switch (strtoupper($s)) {
    case 'EU': return 'EUR';
    case 'US': return 'USA';
    case 'GB': case 'UK': return 'GBR';  // GB is official 2-letter code, altough UK is also used & reserved for other use in ISO
    case 'SE': return 'SWE';
    case 'NO': return 'NOR';
    case 'DE': return 'DEU';
    }
    return $s;
}

/** @param $s 3-letter country code (SWE, NOR) */
function getCountryName($s)
{
    if (is_numeric($s))
        $s = getCountryCode($s);
    else {
        $s = strtoupper($s);

        if (strlen($s) == 2)
            $s = country_2_to_3_letters($s);
    }

    $c3 = array(
    'SWE' => 'Sweden',
    'NOR' => 'Norway',
    'DNK' => 'Denmark',
    'FIN' => 'Finland',
    'USA' => 'United States of America',
    'GBR' => 'United Kingdom',
    'DEU' => 'Germany',

    'EUR' => 'European Union',
    );

    if (!isset($c3[$s]))
        throw new Exception ('Unknown country name '.$s);

    return $c3[$s];
}


/*
// german
    var $country_3char = array(
    'EUR' => 'Europäische Union',
    'SWE' => 'Schweden',
    'NOR' => 'Norwegen',
    'USA' => 'Vereinigte Staaten von Amerika',
    'GBR' => 'Vereinigte Königreich',
    'DEU' => 'Deutschland',
    'DNK' => 'Dänemark',
    );

// Swedish
    var $country_3char = array(
    'EUR' => 'Europeiska Unionen',
    'SWE' => 'Sverige',
    'NOR' => 'Norge',
    'USA' => 'Amerikas förenta stater',
    'GBR' => 'Storbritannien',
    'DEU' => 'Tyskland',
    'DNK' => 'Danmark',
    );
*/

?>
