<?php
/*
 * Frontend wrapper for YUI gallery-ratings
 *
 * https://github.com/petey/yui3-gallery
 * http://yuilibrary.com/gallery/show/ratings
 * http://www.yuiblog.com/blog/2010/04/28/gallery-ratings/
 */

//FIXME: ratings:ratingChange js event  never triggers!

//XXX: make it read-only after you clicked it once

namespace cd;

switch ($this->view) {
case 'handle':
    // handle user rating
    // owner = item type
    // child = item id

    $type = $this->owner;
    $id = $this->child;

    $header->includeJs('http://yui.yahooapis.com/3.4.1/build/yui/yui-min.js');

    $header->includeCss( $page->getRelativeCoreDevUrl().'js/ext/gallery-ratings/assets/gallery-ratings-core.css');

    $widget_id = 'rate_'.mt_rand();

    $max_stars = 5;

    $js =
    'YUI({'.
        'modules:{'.
            '"gallery-ratings":{'.
                'fullpath:"'.relurl( $page->getRelativeCoreDevUrl() ).'js/ext/gallery-ratings/gallery-ratings-min.js",'.
                'requires:["base","widget"]'.
            '}'.
        '},'.
        'filter: "raw"'.
    '}).use("gallery-ratings", function(Y){'.
        'var ratings = new Y.Ratings({'.
            // inline: true,
            // 'allowClearRating: true,'. // shows a "clear rating" button
            // titles: ["1 boot", "2 boots", "3 Feet", "Extra Good", "Great"],
            // 'skin: "small",'.
            'max:'.$max_stars.','.  // total number of stars (default = 5)
            'srcNode: "#'.$widget_id.'"'.
        '});'.

        'Y.log("rator created");'.

        'Y.on("ratings:ratingChange",function(e){'.

            'Y.log("ww2");'.

            'YUI().use("io-base", function(Y){'.
                'var uri = "u/rate/vote/'.$type.'/'.$id.'/" + e.newVal;'.
                'Y.log(uri);'.

                // Subscribe to event "io:complete"
                'Y.on("io:complete", function(id,o){'.
                    'var id = id;'.               // Transaction ID
                    'var data = o.responseText;'. // Response data
                    'if (data==1) return;'.
                    'alert("Voting error " + data);'.
                '});'.

                // Make request
                'var request = Y.io(uri);'.
            '});'.
        '});'.

    '});';

    $avg = Rating::getAverage($type, $id);

    echo
    js_embed($js).
    '<span id="'.$widget_id.'">'.round($avg, 1).'</span>';
    break;

case 'vote':
    // owner = type
    // child = item id
    // child2 = option id

    echo 'WOWOWsls';
    if (!empty($_GET['rate_vote']) && !empty($_GET['opt']))
    {
        if (!$session->id || !is_numeric($_GET['opt']))
            die('XXX');

        $page->disableDesign();

        Rating::addVote($type, $_GET['rate_vote'], $_GET['opt']);

        ob_clean(); // XXX hack.. removes previous output
        die('1');
    }
    break;

default:
    echo 'no such view: '.$this->view;
}


?>
