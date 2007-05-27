<?
	//returns a $pager array with some properties filled
	//reads the 'p' get parameter for current page
	//example: $pager = makePager(102, 25);		will create a pager for total of 102 items with 25 items per page

	function makePager($_total_cnt, $_items_per_page, $_add_value = '')
	{
		$pager['page'] = 1;
		$pager['items_per_page'] = $_items_per_page;
		if (!empty($_GET['p']) && is_numeric($_GET['p'])) $pager['page'] = $_GET['p'];

		$pager['tot_pages'] = round($_total_cnt / $_items_per_page+0.4); // round to closest whole number
		$pager['head'] = 'Page '.$pager['page'].' of '.$pager['tot_pages'].' ('.$_total_cnt.' total)<br/><br/>';

		$pager['index'] = ($pager['page']-1) * $pager['items_per_page'];
		$pager['limit'] = ' LIMIT '.$pager['index'].','.$pager['items_per_page'];

		if ($pager['tot_pages'] <= 1) return $pager;

		if ($pager['page'] > 1) {
			$pager['head'] .= '<a href="'.URLadd('p', $pager['page']-1, $_add_value).'">';
			$pager['head'] .= '<img src="/gfx/arrow_prev.png" alt="Previous" width="11" height="12"/></a>';
		} else {
			$pager['head'] .= '<img src="/gfx/arrow_prev_gray.png" alt="" width="11" height="12"/>';
		}

		for ($i=1; $i <= $pager['tot_pages']; $i++) {
			if ($i==$pager['page']) $pager['head'] .= '<b>';
			$pager['head'] .= ' <a href="'.URLadd('p', $i, $_add_value).'">'.$i.'</a> ';
			if ($i==$pager['page']) $pager['head'] .= '</b>';
		}

		if ($pager['page'] < $pager['tot_pages']) {
			$pager['head'] .= '<a href="'.URLadd('p', $pager['page']+1, $_add_value).'">';
			$pager['head'] .= '<img src="/gfx/arrow_next.png" alt="Next" width="11" height="12"/></a>';
		} else {
			$pager['head'] .= '<img src="/gfx/arrow_next_gray.png" alt="" width="11" height="12"/>';
		}

		return $pager;
	}

	/* Returns the project's path as a "project name" identifier. in a webroot hierarchy if scripts are
			run from the / path it will return nothing, else the directory name of the directory script are run from */
	function getProjectPath($_amp = 1)
	{
		if (!empty($_GET['pr'])) {
			$proj_name = basename(strip_tags($_GET['pr']));
			if ($_amp == 3) return '../'.$proj_name.'/';
		} else {
			if ($_amp == 3) return '';
			$project_path = dirname($_SERVER['SCRIPT_NAME']);
			$pos = strrpos($project_path, '/');
			$proj_name = substr($project_path, $pos+1);
		}

		if ($proj_name) {
			switch ($_amp) {
				case 0: return '?pr='.$proj_name;
				case 1: return '&amp;pr='.$proj_name;
				case 2: return '&pr='.$proj_name;
			}
		}
		return '';
	}

	function URLadd($_key, $_val = '', $_extra = '')
	{
		$arr = parse_url($_SERVER['REQUEST_URI']);

		$wiki_link = false;
		$pos = strpos($_key, ':');
		if ($pos !== false) $wiki_link = substr($_key, $pos+1);

		if ($_val) {
			$keyval = $_key.'='.$_val;
		} else {
			$keyval = $_key;
		}

		if (empty($arr['query'])) return $arr['path'].'?'.$keyval.$_extra;

		$args = explode('&', $arr['query']);

		$out_args = '';

		for ($i=0; $i<count($args); $i++) {
			$vals = explode('=', $args[$i]);
			//Skip it here, $keyval will be added later
			if ($vals[0] == $_key) continue;

			//Wiki:Style links
			if ($wiki_link && strpos($vals[0], ':')) {
				if (substr($vals[0], strpos($vals[0], ':')+1) == $wiki_link) {
					$out_args .= $keyval.'&amp;';	//Replaces wiki link with current wiki link
					$keyval = '';
					continue;
				}
			}

			if (isset($vals[1])) {
				$out_args .= $vals[0].'='.urlencode($vals[1]).'&amp;';
			} else {
				$out_args .= $vals[0].'&amp;';
			}
		}

		if ($out_args && !$keyval && !$_extra) $out_args = substr($out_args, 0, -strlen('&amp;'));

		if ($out_args) {
			return $arr['path'].'?'.$out_args.$keyval.$_extra;
		} else {
			return $arr['path'].'?'.$keyval.$_extra;
		}
	}

	function nameLink($id, $name)
	{
		return '<a href="'.getProjectPath(3).'user.php?id='.$id.'">'.$name.'</a>';
	}

	/* Helper function used to create "are you sure?" pages */
	function confirmed($text, $_var, $_id)
	{
		global $project;	//path to design includes
		global $config, $db, $session, $time_start;

		if (!$_var || !is_numeric($_id) || isset($_GET['confirmed'])) return true;

		require_once($project.'design_head.php');

		echo $text.'<br/><br/>';
		echo '<a href="'.$_SERVER['PHP_SELF'].'?'.$_var.'='.$_id.'&amp;confirmed'.getProjectPath().'">Yes, I am sure</a><br/><br/>';
		echo '<a href="javascript:history.go(-1);">No, wrong button</a><br/>';

		require_once($project.'design_foot.php');
		die;
	}

	/* Takes an array of menu entries and creates a <ul><li>-style menu */
	function createMenu($menu_arr, $class = 'ulli_menu', $current_class = 'ulli_menu_current')
	{
		$cur = basename($_SERVER['SCRIPT_NAME']);

		echo '<ul class="'.$class.'">';
			foreach($menu_arr as $url => $text) {

				//if ($cur == $url || isset($_GET[str_replace('?','',$url)])) echo '<li class="'.$current_class.'">';
				//fixme: highlitar inte wiki parametrar t.ex "Wiki:Hello", "Blog:243"
				if ($cur == $url) echo '<li class="'.$current_class.'">';
				else echo '<li>';

				echo '<a href="'.($url[0] != '/' ? getProjectPath(3) : '').$url.'">'.$text.'</a>';
				echo '</li>';
			}
		echo '</ul>';
	}


	/* Looks for formatted wiki section commands, like: Wiki:Page, WikiEdit:Page, WikiHistory:Page, WikiFiles:Page
		used by functions_wiki.php, functions_blogs.php for special url creation to allow these modules to be embedded in other pages
	*/
	function fetchSpecialParams($allowed_tabs)
	{
		$paramName = '';
		$current_tab = '';

		foreach($_GET as $key => $val) {
			$arr = explode(':', $key);
			if (empty($arr[1]) || !in_array($arr[0], $allowed_tabs)) continue;
			$arr[1] = trim($arr[1]);
			return $arr;
		}

		return false;
	}

	/* Creates a complete XHTML header, showing rss feeds if available, etc
		Uses the following global variables, if they are set:

		$title			- <title> of current page. set the default title with $config['session']['default_title']		todo: rename
		$meta_rss		- array of rss feeds to expose for current page
		$meta_js		- array of javascripts that needs to be included for current page
	
	*/
	function createXHTMLHeader()
	{
		global $session, $title, $meta_js;

		if (!$title) $title = $session->default_title;

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
		echo '<head>';
			echo '<title>'.$title.'</title>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>';
			echo '<link rel="stylesheet" href="'.$session->core_web_root.'css/core.css" type="text/css"/>';
			echo '<link rel="stylesheet" href="'.$session->core_web_root.'css/themes/'.$session->theme.'" type="text/css"/>';
			echo '<link rel="stylesheet" href="'.$session->web_root.'css/site.css" type="text/css"/>';
			linkRSSfeeds();
			echo '<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>';
			echo '<script type="text/javascript" src="'.$session->core_web_root.'js/ajax.js"></script>';
			echo '<script type="text/javascript" src="'.$session->core_web_root.'js/functions.js"></script>';
			if ($meta_js) {
				foreach ($meta_js as $script) {
					echo '<script type="text/javascript" src="'.$script.'"></script>';
				}
			}
		echo '</head>';
		echo '<body>';
		echo '<script type="text/javascript">';
		echo 'var _ext_ref="'.getProjectPath(2).'";';
		echo '</script>';
	}

	/* Called by createXHTMLHeader() to generate xhtml for rss feeds for current page.
	 other pages can add more feeds to $meta_rss before including design */
	function linkRSSfeeds()
	{
		global $meta_rss;

		if (empty($meta_rss)) return;
		
		foreach ($meta_rss as $feed) {
			if (!empty($feed['category']) && is_numeric($feed['category'])) $extra = '?c='.$feed['category'].getProjectPath();
			else $extra = getProjectPath(0);
			echo "\t".'<link rel="alternate" type="application/rss+xml" title="'.$feed['title'].'" href="/core/rss_'.$feed['name'].'.php'.$extra.'"/>'."\n";
		}
	}


	//used by lyrics project only - maybe remove?
	function cleanupText($text)
	{
		global $db;

		$text = trim($text);

		do { /* Remove chunks of whitespace */
			$temp = $text;
			$text = str_replace('  ', ' ', $text);
		} while ($text != $temp);
		
		$text = str_replace('\n', "\n", $text);	//ersätter strängen '\n' mot en linefeed
		$text = str_replace('\r', "\r", $text);	//ersätter strängen '\r' mot en carriage return

		$text = str_replace("\r\n", "\n", $text);
		$text = str_replace(" \n", "\n", $text);
		$text = str_replace("\n ", "\n", $text);

		return $text;
	}
?>