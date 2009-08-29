<?php
/**
 * $Id$
 *
 * Implements a couple of different types of polling functionality
 * used by "site polls" and "polls attached to news articles"
 *
 * @author Martin Lindhe, 2007-2008 <martin@startwars.org>
 */

define('POLL_SITE',		1);	//"Question of the week"-style polls on the site's front page (for example)
define('POLL_NEWS',		2);	//Poll is attached to a news article. ownerId=tblNews.newsId
define('POLL_FORTUNE',	3);	//this is actually a "fortune of the day" message, without options

/**
 * Add a poll
 *
 * @param $duration_mode "day", "week" or numerical number of days
 */
function addPoll($_type, $ownerId, $text, $duration_mode = '', $start_mode = '')
{
	global $h, $db;
	if (!is_numeric($_type) || !is_numeric($ownerId)) return false;

	$text = $db->escape(trim($text));

	switch ($duration_mode) {
		case 'day';
			$length = 1;
			break;

		case 'week':
			$length = 7;
			break;

		case 'month':
			$length = 30;
			break;

		case '': break;

		default:
			if (is_numeric($duration_mode)) $length = $duration_mode;
			else die('eep addpoll');
	}

	switch ($start_mode) {
		case 'thismonday':
			$dayofweek = date('N');
			$mon = date('n');
			$day = date('j');
			$thismonday = mktime(6, 0, 0, $mon, $day - $dayofweek + 1);	//06:00 Monday current week
			$timeStart = ',timeStart="'.sql_datetime($thismonday).'"';
			$timeEnd = ',timeEnd=DATE_ADD(timeStart, INTERVAL '.$length.' DAY)';
			break;

		case 'nextmonday':
			$dayofweek = date('N');
			$mon = date('n');
			$day = date('j');
			$nextmonday = mktime(6, 0, 0, $mon, $day - $dayofweek + 1 + 7);	//06:00 Monday next week
			$timeStart = ',timeStart="'.sql_datetime($nextmonday).'"';
			$timeEnd = ',timeEnd=DATE_ADD(timeStart, INTERVAL '.$length.' DAY)';
			break;

		case 'nextfree':
			$q = 'SELECT timeEnd FROM tblPolls WHERE pollType='.$_type.' AND ownerId='.$ownerId.' AND deletedBy=0 ORDER BY timeStart DESC LIMIT 1';
			$data = $db->getOneRow($q);
			if ($data) {
				$timeStart = ',timeStart="'.$data['timeEnd'].'"';
			} else {
				$timeStart = ',timeStart=NOW()';
			}
			$timeEnd = ',timeEnd=DATE_ADD(timeStart, INTERVAL '.$length.' DAY)';
			break;

		case '':
			$timeStart = '';
			$timeEnd = '';
			break;

		default: die('eexp');
	}

	$q = 'INSERT INTO tblPolls SET ownerId='.$ownerId.',pollType='.$_type.',pollText="'.$text.'",createdBy='.$h->session->id.',timeCreated=NOW()'.$timeStart.$timeEnd;
	return $db->insert($q);
}

/**
 * XXX
 */
function addPollExactPeriod($_type, $ownerId, $text, $_start, $_end)
{
	global $h, $db;
	if (!is_numeric($_type) || !is_numeric($ownerId)) return false;

	$text = $db->escape(trim($text));

	$_start = sql_datetime(strtotime($_start));
	$_end = sql_datetime(strtotime($_end));

	$q = 'INSERT INTO tblPolls SET ownerId='.$ownerId.',pollType='.$_type.',pollText="'.$text.'",createdBy='.$h->session->id.',timeCreated=NOW(),timeStart="'.$db->escape($_start).'",timeEnd="'.$db->escape($_end).'"';
	$db->insert($q);
}

/**
 * Update poll
 */
function updatePoll($_type, $_id, $_text, $timestart = '', $timeend = '')
{
	global $db;
	if (!is_numeric($_type) || !is_numeric($_id)) return false;

	$add_string = '';

	if (!empty($timestart)) $add_string .= ', timeStart = "'.$timestart.'"';
	if (!empty($timeend)) $add_string .= ', timeEnd = "'.$timeend.'"';

	$q = 'UPDATE tblPolls SET pollText="'.$db->escape($_text).'"'.$add_string.' WHERE pollType='.$_type.' AND pollId='.$_id;
	$db->update($q);
}

/**
 * Get all polls
 */
function getPolls($_type, $ownerId = 0)
{
	global $db;
	if (!is_numeric($_type) || !is_numeric($ownerId)) return false;

	$q = 'SELECT * FROM tblPolls WHERE pollType='.$_type.' AND ownerId='.$ownerId.' AND deletedBy=0 ORDER BY timeStart ASC,pollText ASC';
	return $db->getArray($q);
}

/**
 * Get one poll
 */
function getPoll($_type, $_id)
{
	global $db;
	if (!is_numeric($_type) || !is_numeric($_id)) return false;

	$q = 'SELECT * FROM tblPolls WHERE pollType='.$_type.' AND pollId='.$_id.' AND deletedBy=0';
	return $db->getOneRow($q);
}

/**
 * Get active polls
 */
function getActivePolls($_type, $ownerId = 0, $limit = 0)
{
	global $db;
	if (!is_numeric($_type) || !is_numeric($ownerId) || !is_numeric($limit)) return false;

	$q = 'SELECT * FROM tblPolls WHERE pollType='.$_type.' AND ownerId='.$ownerId.' AND deletedBy=0 AND NOW() BETWEEN timeStart AND timeEnd ORDER BY timeStart ASC,pollText ASC';
	if ($limit) $q .= ' LIMIT 0,'.$limit;
	return $db->getArray($q);
}

/**
 * Add poll vote
 */
function addPollVote($_id, $voteId)
{
	global $h, $db;
	if (!is_numeric($_id) || !is_numeric($voteId)) return false;

	$q = 'SELECT userId FROM tblPollVotes WHERE pollId='.$_id.' AND userId='.$h->session->id;
	if ($db->getOneItem($q)) return false;

	$q = 'INSERT INTO tblPollVotes SET userId='.$h->session->id.',pollId='.$_id.',voteId='.$voteId;
	$db->insert($q);
	return true;
}

/**
 * Has current user answered specified poll?
 */
function hasAnsweredPoll($_id)
{
	global $h, $db;
	if (!is_numeric($_id) || !$h->session->id) return false;

	$q = 'SELECT pollId FROM tblPollVotes WHERE userId='.$h->session->id.' AND pollId='.$_id;
	if ($db->getOneItem($q)) return true;
	return false;
}

/**
 * Get statistics for specified poll
 */
function getPollStats($_id)
{
	global $db;
	if (!is_numeric($_id)) return false;

	$q  = 'SELECT t1.categoryName, ';
	$q .= '(SELECT COUNT(*) FROM tblPollVotes WHERE voteId=t1.categoryId) AS cnt ';
	$q .= 'FROM tblCategories AS t1 ';
	$q .= 'WHERE t1.ownerId='.$_id.' AND t1.categoryType='.CATEGORY_POLL;
	return $db->getArray($q);
}

/**
 * Remove poll
 */
function removePoll($_type, $_id)
{
	global $h, $db;
	if (!$h->session->isAdmin || !is_numeric($_type) || !is_numeric($_id)) return false;

	$q = 'UPDATE tblPolls SET deletedBy='.$h->session->id.',timeDeleted=NOW() WHERE pollType='.$_type.' AND pollId='.$_id;
	$db->update($q);
}

/**
 * Polling gadget
 */
function poll($_type, $_id)
{
	global $h;
	if (!is_numeric($_type) || !is_numeric($_id)) return false;

	$data = getPoll($_type, $_id);
	if (!$data) return false;

	$active = false;
	if (time() >= datetime_to_timestamp($data['timeStart']) && time() <= datetime_to_timestamp($data['timeEnd'])) {
		$active = true;
	}
	if (!$data['timeStart']) $active = true;

	$result = '<div class="item">';
	if ($active) $result .= t('Active poll').': ';
	$result .= $data['pollText'].'<br/><br/>';
	$list = getCategories(CATEGORY_POLL, $_id);

	$result .= '<div id="poll'.$_id.'">';
	if ($h->session->isAdmin && $data['timeStart']) $result .= t('Starts').': '.$data['timeStart'].', '.t('ends').' '.$data['timeEnd'].'<br/>';

	if ($h->session->id && $active && !hasAnsweredPoll($_id)) {
		foreach ($list as $row) {
			$result .= '<div class="poll_item" onclick="submit_poll('.$_id.','.$row['categoryId'].')">';
			$result .= $row['categoryName'];
			$result .= '</div><br/>';
		}
	} else {
		if ($h->session->id) {
			$result .= '<br/>';
			if ($active) {
				$result .= t('You already voted, showing current standings').':<br/><br/>';
			} else {
				$result .= t('The poll closed, final result').':<br/><br/>';
			}
		}

		$votes = getPollStats($_id);
		$tot_votes = 0;
		foreach ($votes as $row) $tot_votes += $row['cnt'];

		foreach ($votes as $row) {
			$pct = 0;
			if ($tot_votes) $pct = (($row['cnt'] / $tot_votes)*100);
			$result .= ' &bull; '.$row['categoryName'].' '.t('got').' '.$row['cnt'].' '.t('votes').' ('.$pct.'%)<br/>';
		}
	}

	if ($h->session->isAdmin) {
		$result .= '<br/><input type="button" class="button" value="'.t('Save as .csv').'" onclick="get_poll_csv('.$_id.')"/>';
	}
	$result .= '</div>';

	if ($h->session->id) {
		$result .= '<div id="poll_voted'.$_id.'" style="display:none">';
			$result .= t('Your vote has been registered.');
		$result .= '</div>';
	}

	$result .= '</div>';	//class="item"

	return $result;
}

/**
 * Shows active polls of specified type
 *
 * @param $_type type of poll
 */
function showPolls($_type)
{
	global $db;
	if (!is_numeric($_type)) return false;

	$list = getActivePolls($_type);

	if (!$list) {
		echo t('No polls are currently active');
		return;
	}

	foreach ($list as $row) {
		echo poll($row['pollType'], $row['pollId']);
	}
}

/**
 * Helper function to manage polls, used by admin_polls.php & functions_news.php
 */
function managePolls($_type, $_owner = 0)
{
	if (!is_numeric($_owner)) return false;
	$answer_fields = 8;

	if (!empty($_GET['poll_edit']) && is_numeric($_GET['poll_edit'])) {
		$pollId = $_GET['poll_edit'];

		if (!empty($_POST['poll_q'])) {
			updatePoll($_type, $pollId, $_POST['poll_q']);

			if (!empty($_POST['poll_ts'])) {
				updatePoll($_type, $pollId, $_POST['poll_q'], $_POST['poll_ts']);
			}
			if (!empty($_POST['poll_te'])) {
				updatePoll($_type, $pollId, $_POST['poll_q'], '', $_POST['poll_te']);
			}

			$list = getCategories(CATEGORY_POLL, $pollId);
			for ($i=0; $i<count($list); $i++) {
				if (!empty($_POST['poll_a'.$i])) {
					updateCategory(CATEGORY_POLL, $list[$i]['categoryId'], $_POST['poll_a'.$i]);
				}
			}
			if (!empty($_POST['poll_new_a'])) {
				addCategory(CATEGORY_POLL, $_POST['poll_new_a'], $pollId);
			}
		}

		if (isset($_GET['delete']) && confirmed('Are you sure you want to delete this site poll?', 'delete&amp;id', $pollId)) {
			removePoll($_type, $pollId);
			return;
		}

		$poll = getPoll($_type, $pollId);

		if (!empty($_GET['poll_stats'])) {
			echo '<h1>Poll stats</h1>';

			$votes = getPollStats($pollId);
			$tot_votes = 0;
			foreach ($votes as $row) $tot_votes += $row['cnt'];

			foreach ($votes as $row) {
				$pct = 0;
				if ($tot_votes) $pct = (($row['cnt'] / $tot_votes)*100);
				echo $row['categoryName'].' got '.$row['cnt'].' ('.$pct.'%) votes<br/>';
			}
			return;
		}

		if ($_type == POLL_FORTUNE) {
			echo '<h1>Edit fortune</h1>';
		} else {
			echo '<h1>Edit poll</h1>';
		}

		echo '<form method="post" action="">';
		echo 'Question: ';
		echo xhtmlInput('poll_q', $poll['pollText'], 30).'<br/>';

		echo 'Poll starts: ';
		if (datetime_to_timestamp($poll['timeStart']) < time()) {
			echo $poll['timeStart'].'<br/>';
		} else {
			echo xhtmlInput('poll_ts', $poll['timeStart'], 30).'<br/>';
		}
		echo 'Poll ends: ';

		if (datetime_to_timestamp($poll['timeEnd']) < time()) {
			echo $poll['timeEnd'].'<br/>';
		} else {
			echo xhtmlInput('poll_te', $poll['timeEnd'], 30).'<br/>';
		}
		echo '<br/>';

		if ($poll && $_type != POLL_FORTUNE) {
			$list = getCategories(CATEGORY_POLL, $pollId);
			for ($i=0; $i<count($list); $i++) {
				echo 'Answer '.($i+1).': <input type="text" size="30" name="poll_a'.$i.'" value="'.$list[$i]['categoryName'].'"/><br/>';
			}
			echo 'Add new answer: '.xhtmlInput('poll_new_a', '', 30).'<br/>';
		}

		echo xhtmlSubmit('Save changes');
		echo '</form><br/>';

		echo '<a href="'.URLadd('poll_stats', $pollId).'">Poll stats</a><br/>';
		echo '<a href="'.URLadd('delete&amp;poll_edit', $pollId).'">Delete poll</a><br/>';

		return;
	}

	if (!empty($_POST['poll_q'])) {
		if ($_type == POLL_NEWS) {
			$pollId = addPoll($_type, $_owner, $_POST['poll_q']);
		} else {
			if (!empty($_POST['poll_start_man'])) {
				$pollId = addPollExactPeriod($_type, $_owner, $_POST['poll_q'], $_POST['poll_start_man'], $_POST['poll_end_man']);
			} else {
				$pollId = addPoll($_type, $_owner, $_POST['poll_q'], $_POST['poll_dur'], $_POST['poll_start']);
			}
		}

		for ($i=1; $i<=$answer_fields; $i++) {
			if (!empty($_POST['poll_a'.$i])) {
				addCategory(CATEGORY_POLL, $_POST['poll_a'.$i], $pollId);
			}
		}
	}

	if ($_type == POLL_FORTUNE) {
		$title = 'Add new fortune';
	} else {
		$title = 'Add new poll';
	}

	echo '<h2 onclick="toggle_element_by_name(\'new_poll_form\')">'.$title.'</h2>';
	echo '<div id="new_poll_form" style="display: none;">';
	echo '<form method="post" action="">';
	echo 'Question: '.xhtmlInput('poll_q', '', 30).'<br/>';
	if ($_type == POLL_SITE || $_type == POLL_FORTUNE) {
		echo '<div id="poll_period_selector">';
		echo 'Duration of the poll: ';
		echo '<select name="poll_dur">';
		echo '<option value="day">1 day</option>';
		echo '<option value="week" selected="selected">1 week</option>';
		echo '<option value="month">1 month</option>';
		echo '</select><br/>';

		echo 'Poll start: ';
		echo '<select name="poll_start">';
		echo '<option value="thismonday">monday this week</option>';
		echo '<option value="nextmonday">monday next week</option>';
		echo '<option value="nextfree"'.(count($list)?' selected="selected"':'').'>next free time</option>';
		echo '</select><br/>';
		echo '<a href="#" onclick="hide_element_by_name(\'poll_period_selector\');show_element_by_name(\'poll_period_manual\')">Enter dates manually</a>';
		echo '</div>';
		echo '<div id="poll_period_manual" style="display: none;">';
			echo 'Start time: '.xhtmlInput('poll_start_man').' (format YYYY-MM-DD HH:MM)<br/>';
			echo 'End time: '.xhtmlInput('poll_end_man').'<br/>';
			echo '<a href="#" onclick="hide_element_by_name(\'poll_period_manual\');show_element_by_name(\'poll_period_selector\')">Use dropdown menus instead</a>';
		echo '</div>';
		echo '<br/><br/>';
	}

	if ($_type != POLL_FORTUNE) {
		for ($i=1; $i<=$answer_fields; $i++) {
			echo t('Answer').' '.$i.': '.xhtmlInput('poll_a'.$i, '', 30).'<br/>';
		}
	}

	echo xhtmlSubmit('Create');
	echo '</form>';
	echo '</div>';

	switch ($_type) {
		case POLL_SITE:
			echo '<h1>Site polls</h1>';
			break;

		case POLL_NEWS:
			echo '<h1>News polls</h1>';
			break;


		case POLL_FORTUNE:
			echo '<h1>Fortunes</h1>';
			break;

		default: die('managePolls() EEP');
	}

	$list = getPolls($_type, $_owner);
	if (count($list)) {
		echo '<table>';
		echo '<tr>';
		echo '<th>Title</th>';
		if ($_type == POLL_SITE) {
			echo '<th>Starts</th>';
			echo '<th>Ends</th>';
		}
		echo '</tr>';
	}

	foreach ($list as $row) {
		if ($_type == POLL_SITE) {
			$expired = $active = false;
			if (time() > datetime_to_timestamp($row['timeEnd'])) $expired = true;
			if (time() >= datetime_to_timestamp($row['timeStart']) && !$expired) $active = true;

			if ($expired) {
				echo '<tr style="font-style: italic">';
			} else if ($active) {
				echo '<tr style="font-weight: bold">';
			} else {
				echo '<tr>';
			}
		} else {
			echo '<tr>';
		}

		echo '<td><a href="'.URLadd('poll_edit', $row['pollId']).'">'.$row['pollText'].'</a></td>';

		if ($_type == POLL_SITE) {
			echo '<td>'.$row['timeStart'].'</td>';
			echo '<td>'.$row['timeEnd'].'</td>';
		}
		echo '</tr>';
	}
	if (count($list)) echo '</table>';
	echo '<br/>';
}

/**
 * Helper function for displaying polls attached to a news article
 */
function showAttachedPolls($_type, $_owner)
{
	global $h, $db;
	if (!$h->session->isAdmin || !is_numeric($_type) || !is_numeric($_owner)) return false;

	$list = getPolls($_type, $_owner);
	if (!$list) return false;

	$res = '';

	foreach ($list as $row) {
		$res .= '<div class="poll_attached">';
		$res .= '<b>'.$row['pollText'].'</b><br/>';
		$answers = getCategories(CATEGORY_POLL, $row['pollId']);
		foreach ($answers as $an) {
			$res .= $an['categoryName'].' ';
		}
		$res .= '<a href="">See results</a>';
		$res .= '</div>';
	}

	return $res;
}
?>