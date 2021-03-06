<?php
/**
 * $Id$
 *
 * Video handling helper functions
 *
 * @author Martin Lindhe, 2007-2011 <martin@ubique.se>
 */

//STATUS: deprecated... use embed_flv() in html.php to embed a flash video/audio player instead

/**
 * Embeds a video in a html page using Windows Media Player
 *
 * All parameters are documented here:
 * http://www.mioplanet.com/rsc/embed_mediaplayer.htm
 *
 * Common parameters:
 *    AutoStart    true/false
 *    uiMode        invisible, none, mini, full
 *    fullScreen    true/false
 *    PlayCount    numeric
 *
 * Windows Media Player 6.4: "clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
 *
 * @param $url video url to embed
 * @param $w width of player window (not width of video clip)
 * @param $h height of player window (not height of video clip)
 * @return html code
 */
function embedVideo($url, $width = 352, $height = 288, $params = array())
{
    if (!is_numeric($width) || !is_numeric($height)) return false;

    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')) {
        //Tested in IE 7
        //FIXME try IE 6
        $data  = '<object type="application/x-oleobject'.
                ' width="'.$width.'" height="'.$height.'"'.
                ' classid="clsid:6BF52A52-394A-11D3-B153-00C04F79FAA6">';    //Windows Media Player 7, 9, 10 and 11
        $data .= '<param name="URL" value="'.$url.'">';
        if (empty($params)) {
            //default settings
            $data .= '<param name="AutoStart" value="false">';
            $data .= '<param name="uiMode" value="mini">';
        } else {
            foreach ($params as $name => $val) {
                $data .= '<param name="'.$name.'" value="'.$val.'">';
            }
        }
        $data .= '</object>';
    } else {
        //This works with Firefox in Windows and Linux and Opera in Windows
        //For Firefox Linux, install mozilla-plugin-vlc
        //For Firefox Windows, install wmpfirefoxplugin.exe from http://port25.technet.com
        $data = '<embed type="application/x-mplayer2"'.
                ' width="'.$width.'" height="'.$height.'"'.
                ' src="'.$url.'"'.
                ' ShowControls="1" ShowStatusBar="1"'.
                ' autostart="'.(!empty($params['AutoStart']) && $params['AutoStart'] == 'true' ? '1' : '0').'">';
        $data .= '</embed>';
    }

    return $data;
}

/**
 * XXX
 */
function embedAudio($url, $width = 352)
{
    if (!is_numeric($w)) return false;

    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')) {
        $height = 46;
        //Tested in IE 7
        //FIXME try IE 6
        $data  = '<object type="application/x-oleobject'.
                ' width="'.$width.'" height="'.$height.'"'.
                ' classid="clsid:6BF52A52-394A-11D3-B153-00C04F79FAA6">';    //Windows Media Player 7, 9, 10 and 11
        $data .= '<param name="URL" value="'.$url.'">';

        //default settings
        $data .= '<param name="AutoStart" value="false">';
        $data .= '<param name="uiMode" value="mini">';
        $data .= '</object>';
    } else {
        $height = 50;
        //This works with Firefox in Windows and Linux and Opera in Windows
        //For Firefox Linux, install mozilla-plugin-vlc
        //For Firefox Windows, install wmpfirefoxplugin.exe from http://port25.technet.com
        $data = '<embed type="application/x-mplayer2"'.
                ' width="'.$width.'" height="'.$height.'"'.
                ' src="'.$url.'"'.
                ' ShowControls="1" ShowStatusBar="1"'.
                ' autostart="0">';
        $data .= '</embed>';
    }

    return $data;
}



/**
 * Helper function for embedding quicktime video
 * Requires Apple's AC_QuickTime.js from http://developer.apple.com/internet/ieembedprep.html
 * Parameter docs: http://www.apple.com/quicktime/tutorials/embed2.html
 *
 * @param $url url to embed
 * @param $mute bool. set to true to mute audio
 */
function embedQuickTimeVideo($url, $mute = false)
{
    $width = 176;
    $height = 144 + 16;    //some extra pixels for media player controller

    $fake_url = 'http://10.10.10.240/xinfo.php';    //FIXME file must exist

    $data  = '<script language="javascript" type="text/javascript">';
    $data .= 'QT_WriteOBJECT_XHTML("'.$fake_url.'", "'.$width.'", "'.$height.'", "", ';
        $data .= '"qtsrc", "'.$url.'", ';
        $data .= '"controller", "true", ';
        $data .= '"target", "myself", ';
        $data .= '"type", "video/quicktime", ';
        $data .= '"kioskmode", "true", ';        //hides right-click menu
        if ($mute) $data .= '"volume", "0", ';
        $data .= '"autoplay", "true"';
    $data .= ');';
    $data .= '</script>';

    return $data;
}

?>
