<?php
/**
 * $Id$
 *
 * Parses an ASX playlist into VideoResource objects
 *
 * http://en.wikipedia.org/wiki/Advanced_Stream_Redirector
 *
 * @author Martin Lindhe, 2008-2011 <martin@startwars.org>
 */

//STATUS: ok, needs more testing

//TODO: extend from HttpClient

/* FIXME test: only tried it with 1-entry files:
<asx version="3.0">
  <entry>
    <ref href="mms://wm0.c90901.cdn.qbrick.com/90901/kluster/20091021/PG-1133804-003A-BOOMSHAKALACK2-02.wmv"/>
    <author>svt.se</author>
    <copyright>Sveriges Television AB 2009</copyright>
  </entry>
</asx>
*/

namespace cd;

require_once('CoreBase.php');
require_once('HttpClient.php');
require_once('MediaResource.php');

class AsxReader extends CoreBase
{
    private $items = array(); ///< MediaItem objects

    /**
     * @return array of MediaItem objects
     */
    function getItems() { return $this->items; }

    function parse($data)
    {
        if (is_url($data)) {
            $u = new HttpClient($data);
            $u->setCacheTime(60 * 60); //1h
            $data = $u->getBody();

            //FIXME check http client return code for 404
            if (substr($data, 0, 5) != '<asx ') {
                dp('input_asx->parse FAIL: cant parse playlist from '.$u->getUrl() );
                return false;
            }
        }

        $reader = new XMLReader();
        if ($this->getDebug()) echo 'Parsing ASX: '.$data.ln();
        $reader->xml($data);

        $item = new VideoResource();

        while ($reader->read())
        {
            if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->name == 'asx') {
                $this->items[] = $item;
                $item = new VideoResource();
            }

            if ($reader->nodeType != XMLReader::ELEMENT)
                continue;

            switch ($reader->name) {
            case 'asx':
                if ($reader->getAttribute('version') != '3.0')
                    die('XXX FIXME unsupported ASX version '.$reader->getAttribute('version') );
                break;

            case 'entry':
                while ($reader->read()) {
                    if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->name == 'entry')
                        break;

                    if ($reader->nodeType != XMLReader::ELEMENT)
                        continue;

                    switch ($reader->name) {
                    case 'author': break; //<author>svt.se</author>
                    case 'copyright': break; //<copyright>Sveriges Television AB 2009</copyright>
                    case 'starttime': break; //<starttime value="00:00:00.00"/>

                    case 'ref': //<ref href="mms://wm0.c90901.cdn.qbrick.com/90901/kluster/20091026/aekonomi920.wmv"/>
                        $item->setUrl( $reader->getAttribute('href') );
                        break;

                    case 'duration': //<duration value="00:03:39.00"/>
                        $item->setDuration( $reader->getAttribute('value') );
                        break;

                    default:
                        echo "bad entry " .$reader->name.ln();
                    }
                }
                break;
            default:
                echo "unknown ".$reader->name.ln();
                break;
            }
        }

        $reader->close();
        return true;
    }
}

?>
