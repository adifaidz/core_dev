<?php
/**
 * $Id$
 *
 * Set of functions to implement revisioned backups of data, used by various modules
 *
 * @author Martin Lindhe, 2007-2008 <martin@startwars.org>
 */

//revision types:
define('REVISIONS_WIKI', 1);

//revision categories:
define('REV_CAT_TEXT_CHANGED',  1);
define('REV_CAT_FILE_UPLOADED', 2);
define('REV_CAT_FILE_DELETED',  3);
define('REV_CAT_LOCKED',        4);
define('REV_CAT_UNLOCKED',      5);

/**
 * @todo kanske kunna minska ner antalet parametrar på nåt sätt?
 */
function addRevision($fieldType, $fieldId, $fieldText, $timestamp, $creatorId, $categoryId = 0)
{
    global $db;

    if (!is_numeric($fieldType) || !is_numeric($fieldId) || !is_numeric($creatorId) || !is_numeric($categoryId)) return false;

    $timestamp = $db->escape($timestamp);        //todo: validate timestamp bättre

    $q = 'INSERT INTO tblRevisions SET fieldId='.$fieldId.',fieldType='.$fieldType.',fieldText="'.$db->escape($fieldText).'",createdBy='.$creatorId.',timeCreated="'.$timestamp.'",categoryId='.$categoryId;
    return $db->insert($q);
}

/**
 *
 */
function showRevisions($articleType, $articleId, $articleName)
{
    global $db;

    if (!is_numeric($articleType) || !is_numeric($articleId)) return false;

    echo 'History of article '.$articleName.'<br/><br/>';

    $q = 'SELECT COUNT(*) FROM tblRevisions WHERE fieldId='.$articleId.' AND fieldType='.$articleType;
    $tot_cnt = $db->getOneItem($q);
    $pager = makePager($tot_cnt, 5);

    $q  = 'SELECT * FROM tblRevisions ';
    $q .= 'WHERE fieldId='.$articleId.' AND fieldType='.$articleType;
    $q .= ' ORDER BY timeCreated DESC'.$pager['limit'];
    $list = $db->getArray($q);

    echo $pager['head'];

    if (!$list) {
        echo '<b>There is no edit history of this wiki in the database.</b><br/>';
        return;
    }

    foreach ($list as $row) {
        echo formatTime($row['timeCreated']).': ';
        switch ($row['categoryId']) {
            case REV_CAT_LOCKED:
                echo '<img src="'.coredev_webroot().'gfx/icon_locked.png" width="16" height="16" alt="Locked"/>';
                echo ' Locked by '.Users::getName($row['createdBy']).'<br/>';
                break;

            case REV_CAT_UNLOCKED:
                echo '<img src="'.coredev_webroot().'gfx/icon_unlocked.png" width="16" height="16" alt="Unlocked"/>';
                echo ' Unlocked by '.Users::getName($row['createdBy']).'<br/>';
                break;

            case REV_CAT_FILE_UPLOADED:
                echo ' File uploaded by '.Users::getName($row['createdBy']).'<br/>';
                break;

            case REV_CAT_FILE_DELETED:
                echo ' File deleted by '.Users::getName($row['createdBy']).'<br/>';
                break;

            case REV_CAT_TEXT_CHANGED:
            default:
                echo '<a href="#" onclick="return toggle_element(\'layer_history'.$row['indexId'].'\')">';
                echo t('Edited by').' '.Users::getName($row['createdBy']). ' ('.strlen($row['fieldText']).' '.t('characters').')</a><br/>';
                echo '<div id="layer_history'.$row['indexId'].'" class="revision_entry" style="display: none;">';
                echo nl2br(htmlentities($row['fieldText'], ENT_COMPAT, 'UTF-8'));
                echo '</div>';
                break;
        }
    }
}
?>
