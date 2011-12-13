<?php

//STATUS: broken!! cleanup

/*
    static function renderActivePolls($type)
    {
        $res = '';
        foreach (self::getActivePolls($type) as $poll)
            $res .= self::renderPoll($type, $poll['owner']);

        return $res;
    }
*/
    static function renderPoll($type, $id)
    {
        if (!$id)
            throw new Exception ('no id set');

        $data = self::getPoll($id);
        if (!$data)
            return false;

        $session = SessionHandler::getInstance();

        if (!empty($_GET['poll_vote']) && !empty($_GET['opt']))
        {
            if (!$session->id || !is_numeric($_GET['poll_vote']) || !is_numeric($_GET['opt']))
                die('XXX');

            $page = XmlDocumentHandler::getInstance();
            $page->disableDesign();

            self::addPollVote($type, $_GET['poll_vote'], $_GET['opt']);

            ob_clean(); // XXX hack.. removes previous output
            die('1');
        }

        $header = XhtmlHeader::getInstance();

        $header->embedCss(
        '.poll_item{'.
            'background-color:#eee;'.
            'padding:5px;'.
            'cursor:pointer;'.
        '}'
        );

        $header->includeJs('http://yui.yahooapis.com/3.3.0/build/yui/yui-min.js');

        $header->embedJs(
        //Makes element with name "n" invisible in browser
        'function hide_element(n)'.
        '{'.
            'var e = document.getElementById(n);'.
            'e.style.display = "none";'.
        '}'.
        //Makes element with name "n" visible in browser
        'function show_element(n)'.
        '{'.
            'var e = document.getElementById(n);'.
            'e.style.display = "";'.
        '}'.

        'function submit_poll(id,opt)'.
        '{'.
            'YUI().use("io-base", function(Y) {'.
                'var uri = "?poll_vote=" + id + "&opt=" + opt;'.

                // Define a function to handle the response data
                'function complete(id, o, args) {'.
                    'var id = id;'.               // Transaction ID
                    'var data = o.responseText;'. // Response data
                    'var args = args[1];'.
                    'if (data==1) return;'.
                    'alert("Voting error " + data);'.
                '};'.

                // Subscribe to event "io:complete", and pass an array
                // as an argument to the event handler "complete", since
                // "complete" is global.   At this point in the transaction
                // lifecycle, success or failure is not yet known.
                'Y.on("io:complete", complete, Y, ["lorem", "ipsum"]);'.

                // Make request
                'var request = Y.io(uri);'.
            '});'.

            'hide_element("poll"+id);'.
            'show_element("poll_voted"+id);'.
        '}'
        );

        $active = false;
        if (time() >= ts($data['timeStart']) && time() <= ts($data['timeEnd']))
            $active = true;

        if (!$data['timeStart'])
            $active = true;

        $res = '<div class="item">';
        if ($active)
            $res .= 'Active poll: ';

        $res .= $data['pollText'].'<br/><br/>';

        $res .= '<div id="poll'.$id.'">';
        if ($data['timeStart'])
            $res .= 'Starts: '.$data['timeStart'].', ends '.$data['timeEnd'].'<br/>';

        if ($session->id && $active && !self::hasAnsweredPoll($id))
        {
            $cats = new CategoryList( POLL );
            $cats->setOwner($id);

            $list = $cats->getItems();

            if (!$list)
                echo '<div class="critical">No options is available to this poll!</div>';
            else if (count($list) == 1)
                echo '<div class="critical">Only one options is available to this poll!</div>';
            else
                foreach ($list as $opt)
                    $res .=
                    '<div class="poll_item" onclick="submit_poll('.$id.','.$opt->id.')">'.
                        $opt->title.
                    '</div><br/>';

        } else {
            if ($session->id) {
                $res .= '<br/>';
                if ($active) {
                    $res .= 'You already voted, showing current standings:<br/><br/>';
                } else {
                    $res .= 'The poll closed, final result:<br/><br/>';
                }
            }

            $votes = self::getPollStats($id);

            $tot_votes = 0;
            foreach ($votes as $cnt)
                $tot_votes += $cnt;

            $list = array();
            foreach ($votes as $title => $cnt) {
                $pct = 0;
                if ($tot_votes)
                    $pct = (($cnt / $tot_votes)*100);
                $res .= ' &bull; '.$title.' got '.$cnt.' votes ('.$pct.'%)<br/>';

                $list[] = array('name' => $title, 'value' => $cnt);
            }

            $pie = new Yui3PieChart();
            $pie->setWidth(100);
            $pie->setHeight(100);
            $pie->setCategoryKey('name');
            $pie->setDataSource($list);

            $res .= $pie->render();
        }

        $res .= '</div>';

        if ($session->id) {
            $res .=
            '<div id="poll_voted'.$id.'" style="display:none">'.
                'Your vote has been registered.'.
            '</div>';
        }

        $res .= '</div>';    //class="item"

        return $res;
    }

?>
