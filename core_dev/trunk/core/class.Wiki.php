<?php
/**
 * $Id$
 *
 * @author Martin Lindhe, 2007-2010 <martin@startwars.org>
 */

require_once('atom_revisions.php');
require_once('functions_textformat.php');

//STATUS: wip - cleanup & rewrite code

//TODO: make a render view

//TODO: reimplement file attachments
//TODO: use yui_richedit (?)

class Wiki
{
    private $id, $name, $text;
    private $editorId, $lockerId;
    private $timestamp, $timeLocked;

    private $tabs    = array('Wiki', 'WikiEdit', 'WikiHistory');
    var $first_tab   = 'Wiki';
    var $allow_edit  = false;  ///< false = only allow admins to edit the wiki articles. true = allow all, even anonymous
    var $allow_html  = false;  ///< allow html code in the wiki article? VERY UNSAFE to allow others do this

    function getName() { return $this->name; }

    function getId() { return $this->id; }
    function getText() { return $this->text; }
    function getTabs() { return $this->tabs; }

    function enableHtml() { $this->allow_html = true; }

    /**
     * Sets wiki name formated for tblWiki.wikiName
     * "Install Guide" => "Install_Guide"
     */
    function setName($n)
    {
        $n = normalizeString($n, array("\t"));
        $n = str_replace(' ', '_', $n);

        $this->name = $n;
    }

    function __construct($name = '')
    {
        $this->setName($name);
    }

    function load()
    {
        if (!$this->name) return false;

        $db = SqlHandler::getInstance();

        $q =
        'SELECT * FROM tblWiki AS t1'.
        ' WHERE wikiName="'.$db->escape($this->name).'"';
        $data = $db->getOneRow($q);
        if (!$data) return false;

        $this->name       = $data['wikiName'];
        $this->id         = $data['wikiId'];
        $this->text       = $data['msg'];
        $this->editorId   = $data['createdBy'];     //XXX rename tblWiki.createdBy to .editorId
        $this->lockerId   = $data['lockedBy'];   //XXX rename to lockerId
        $this->timestamp  = $data['timeCreated'];//XXX rename to timestamp
        $this->timeLocked = $data['timeLocked'];
        return true;
    }

    /**
     * Formats text for wiki output
     */
    function formatText()
    {
        $session = SessionHandler::getInstance();

        if (empty($this->text)) {
            $res = t('The wiki').' "'.$this->name.'" '.t('does not yet exist').'!<br/>';
            if ($session->id && $session->isWebmaster)
                $res .= coreButton('Create', '?WikiEdit:'.$this->name);

            return $res;
        }

        $res = stripslashes($this->text);
        $res = formatUserInputText($res, !$this->allow_html);

        return $res;
    }

    /**
     * Update wiki entry
     */
    function update($text)
    {
        $db = SqlHandler::getInstance();
        $session = SessionHandler::getInstance();

        $text = trim($text);

        $q = 'SELECT * FROM tblWiki WHERE wikiName="'.$db->escape($this->name).'"';
        $data = $db->getOneRow($q);

        //Aborts if we are trying to save a exact copy as the last one
        if (!empty($data) && $data['msg'] == $text) return false;

        $this->text      = $text;
        $this->timestamp = time();

        if (!empty($data) && $data['wikiId']) {
            addRevision(REVISIONS_WIKI, $data['wikiId'], $data['msg'], $data['timeCreated'], $data['createdBy'], REV_CAT_TEXT_CHANGED);

            $q = 'UPDATE tblWiki SET msg="'.$db->escape($this->text).'",createdBy='.$session->id.',revision=revision+1,timeCreated=NOW() WHERE wikiName="'.$db->escape($this->name).'"';
            $db->update($q);
            return;
        }
        $q = 'INSERT INTO tblWiki SET wikiName="'.$db->escape($this->name).'",msg="'.$db->escape($this->text).'",createdBy='.$session->id.',revision=1,timeCreated=NOW()';
        $db->insert($q);
    }

    function render()
    {
        $db = SqlHandler::getInstance();
        $session = SessionHandler::getInstance();

        $header = XhtmlHeader::getInstance();

        $header->addCss('
        .wiki {
         font-size: 14px;
        }
        .wiki_body {
         padding: 10px;
         background-color: #F0F0F0;
         color: #000;
         border-width: 1px;
         border-left-style: solid;
         border-right-style: solid;
         border-bottom-style: solid;
         border-top-style: solid;
        }
        .wiki_locked {
         padding: 5px;
         padding-left: 25px;
         font-size: 20px;
         background: #ee99aa url("../../gfx/icon_locked.png") no-repeat;
         background-position: 5px 50%;
        }
        .wiki_menu {
         font-size: 12px;
         margin-top: 0;
         padding-left: 0;
        }
        .wiki_menu li {
         margin-left: 2px;
         margin-right: 2px;
         display: inline;
         border: 1px #000 solid;
         background-color: #ddd;
         padding: 4px;

        }
        .wiki_menu li a {
         color: #000;
         text-decoration: none;
        }
        .wiki_menu li:hover {
         background-color: #fff;
        }');


        $current_tab = $this->first_tab;

        //Looks for formatted wiki section commands: Wiki:Page, WikiEdit:Page, WikiHistory:Page, WikiFiles:Page
        $cmd = fetchSpecialParams($this->tabs);
        if ($cmd) {
            list($current_tab, $name) = $cmd;
            $this->setName($name);
        }

        //loads the wiki to display
        $this->load();

        if (!$this->name) return false;

        if (!$this->lockerId && isset($_POST['wiki_'.$this->id])) {
            //save changes
            $this->update($_POST['wiki_'.$this->id]);
            unset($_POST['wiki_'.$this->id]);
        }

        if ($session->isAdmin && !empty($_GET['wikilock'])) {
            $q = 'UPDATE tblWiki SET lockedBy='.$session->id.',timeLocked=NOW() WHERE wikiId='.$this->id;
            $db->update($q);
            $this->lockerId = $session->id;
            addRevision(REVISIONS_WIKI, $this->id, 'The wiki has been locked', now(), $session->id, REV_CAT_LOCKED);
        } else if ($session->isAdmin && isset($_GET['wikilock'])) {
            $q = 'UPDATE tblWiki SET lockedBy=0 WHERE wikiId='.$this->id;
            $db->update($q);
            $this->lockerId = 0;
            addRevision(REVISIONS_WIKI, $this->id, 'The wiki has been unlocked', now(), $session->id, REV_CAT_UNLOCKED);
        }

        //Only display the text for normal visitors
        if (!$session->isAdmin && !$this->allow_edit) {
            echo '<div class="wiki">';
            echo '<div class="wiki_body">'.$this->formatText().'</div>';
            echo '</div>';
            return;
        }

        echo '<div class="wiki">';

        $menu = new XhtmlMenu();
        $menu->setCss('wiki_menu');
        $menu->add('Wiki:'.str_replace('_', ' ', $this->name), '/wiki/?Wiki:'.$this->name);
        $menu->add(t('Edit'), '/wiki/?WikiEdit:'.$this->name);
        $menu->add(t('History'), '/wiki/?WikiHistory:'.$this->name);
        echo $menu->render();

        //echo xhtmlMenu($wiki_menu, 'wiki_menu');
        echo '<div class="wiki_body">';

        //Display the wiki toolbar for admins
        if ($current_tab == 'WikiEdit' && ($session->isAdmin || !$this->lockerId)) {

            echo xhtmlForm('wiki_edit', URLadd('WikiEdit:'.$this->name));

            $wikiRandId = 'wiki_'.$this->id.'_'.rand(0, 9999999);

            echo
                '<input type="button" class="button" value="[h1]" onclick="insertTags(\''.$wikiRandId.'\',\'[h1]\',\'[/h1]\',\'headline level 1\')"/>'.
                '<input type="button" class="button" value="[h2]" onclick="insertTags(\''.$wikiRandId.'\',\'[h2]\',\'[/h2]\',\'headline level 2\')"/>'.
                '<input type="button" class="button" value="[h3]" onclick="insertTags(\''.$wikiRandId.'\',\'[h3]\',\'[/h3]\',\'headline level 3\')"/>'.
                '<input type="button" class="button" value="B" style="font-weight: bold" onclick="insertTags(\''.$wikiRandId.'\',\'[b]\',\'[/b]\',\'bold text\')"/>'.
                '<input type="button" class="button" value="I" style="font-style: italic" onclick="insertTags(\''.$wikiRandId.'\',\'[i]\',\'[/i]\',\'italic text\')"/>'.
                '<input type="button" class="button" value="U" style="text-decoration: underline" onclick="insertTags(\''.$wikiRandId.'\',\'[u]\',\'[/u]\',\'underlined text\')"/>'.
                '<input type="button" class="button" value="S" style="text-decoration: line-through" onclick="insertTags(\''.$wikiRandId.'\',\'[s]\',\'[/s]\',\'strikethru text\')"/>'.
                //'<input type="button" class="button" value="[hr] (broken)" onclick="insertTags(\''.$wikiRandId.'\',\'[hr]\')"/>'.
                '<input type="button" class="button" value="[code]" onclick="insertTags(\''.$wikiRandId.'\',\'[code]\',\'[/code]\',\'code block\')"/>'.
                '<input type="button" class="button" value="[raw]" onclick="insertTags(\''.$wikiRandId.'\',\'[raw]\',\'[/raw]\',\'raw block\')"/>'.
                '<input type="button" class="button" value="[quote]" onclick="insertTags(\''.$wikiRandId.'\',\'[quote name=]\',\'[/quote]\',\'quote\')"/>'.
                '<br/>';

            if ($this->lockerId)
                echo '<div class="wiki_locked">This article is currently locked from editing.</div>';

            $rows = 8+substr_count($this->text, "\n");
            if ($rows > 36) $rows = 36;

            echo '<textarea name="wiki_'.$this->id.'" id="'.$wikiRandId.'" cols="60" rows="'.$rows.'"'.($this->lockerId ? ' readonly': '').'>'.$this->text.'</textarea><br/>';

            echo t('Last edited').' ';
            if ($this->timestamp) echo formatTime($this->timestamp).' '.t('by').' '.Users::getName($this->editorId);
            else echo t('never');
            echo '<br>';

            echo xhtmlSubmit('Save');

            if ($session->isAdmin) {
                if ($this->lockerId) {
                    echo '<input type="button" class="button" value="'.t('Unlock').'" onclick="location.href=\''.URLadd('WikiEdit:'.$this->name, '&amp;wikilock=0').'\'"/>';
                    echo xhtmlImage(coredev_webroot().'gfx/icon_locked.png', 'This wiki is currently locked');
                    echo '<b>Locked by '.Users::getName($this->lockerId).' at '.formatTime($this->timeLocked).'</b><br/>';
                } else if ($this->text) {
                    echo '<input type="button" class="button" value="'.t('Lock').'" onclick="location.href=\''.URLadd('WikiEdit:'.$this->name, '&amp;wikilock=1').'\'"/>';
                    echo xhtmlImage(coredev_webroot().'gfx/icon_unlocked.png', 'This article is open for edit by anyone');
                }
            }

            echo xhtmlFormClose();
        } else if ($current_tab == 'WikiHistory') {
            if ($this->text) {
                echo t('Current version').':<br/>';
                echo '<b><a href="#" onclick="return toggle_element(\'layer_history_current\')">'.t('Edited').' '.formatTime($this->timestamp).' '.t('by').' '.Users::getName($this->editorId).' ('.strlen($this->text).' '.t('characters').')</a></b><br/>';
                echo '<div id="layer_history_current" class="revision_entry">';
                echo nl2br(htmlentities($this->text, ENT_COMPAT, 'UTF-8'));
                echo '</div>';

                showRevisions(REVISIONS_WIKI, $this->id, $this->name);
            } else {
                echo 'There is no history for this wiki.';
            }
        } else {
            echo $this->formatText();
        }

        echo     '</div>';
        echo '</div>';
    }
}
