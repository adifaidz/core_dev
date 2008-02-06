<?
/**
 * $Id$
 *
 * Functions assumed to always be available
 *
 * \author Martin Lindhe, 2007-2008 <martin@startwars.org>
 */

	/**
	 * Debug function. Prints out variable $v
	 *
	 * \param $v variable of any type to display
	 * \return nothing
   */
	function d($v)
	{
		if (is_string($v)) echo htmlentities($v);
		else {
			if (extension_loaded('xdebug')) var_dump($v);	//xdebug's var_dump is awesome
			else {
				echo '<pre>';
				print_r($v);
				echo '</pre>';
			}
		}
	}

	/**
	 * Helper function to include core function files
	 *
	 * \param $file filename to include
	 */
	function require_core($file)
	{
		global $config;
		require_once($config['core_root'].'core/'.$file);
	}

	/* loads all active plugins */
	function loadPlugins()
	{
		global $config;

		if (empty($config['plugins'])) return;

		foreach ($config['plugins'] as $plugin) {
			require_once($config['core_root'].'plugins/'.$plugin.'/plugin.php');
		}
	}

	/**
	 * Helper function for generating "pagers", splitting up listings of content on several pages
	 *
	 * Reads the 'p' get parameter for current page
	 * Example: $pager = makePager(102, 25);		will create a pager for total of 102 items with 25 items per page
	 *
	 * \return Returns a $pager array with some properties filled
	 */
	function makePager($_total_cnt, $_items_per_page, $_add_value = '')
	{
		global $config;

		$pager['page'] = 1;
		$pager['items_per_page'] = $_items_per_page;
		if (!empty($_GET['p']) && is_numeric($_GET['p'])) $pager['page'] = $_GET['p'];

		$pager['tot_pages'] = round($_total_cnt / $_items_per_page+0.4); // round to closest whole number
		if ($pager['tot_pages'] < 1) $pager['tot_pages'] = 1;
		$pager['head'] = 'Page '.$pager['page'].' of '.$pager['tot_pages'].' (displaying '.$_total_cnt.' items)<br/><br/>';

		$pager['index'] = ($pager['page']-1) * $pager['items_per_page'];
		$pager['limit'] = ' LIMIT '.$pager['index'].','.$pager['items_per_page'];

		if ($pager['tot_pages'] <= 1) return $pager;

		if ($pager['page'] > 1) {
			$pager['head'] .= '<a href="'.URLadd('p', $pager['page']-1, $_add_value).'">';
			$pager['head'] .= '<img src="'.$config['core_web_root'].'gfx/arrow_prev.png" alt="Previous" width="11" height="12"/></a>';
		//} else {
		//	$pager['head'] .= '<img src="'.$config['core_web_root'].'gfx/arrow_prev_gray.png" alt="" width="11" height="12"/>';
		}

		for ($i=1; $i <= $pager['tot_pages']; $i++) {
			if ($i==$pager['page']) $pager['head'] .= '<b>';
			$pager['head'] .= ' <a href="'.URLadd('p', $i, $_add_value).'">'.$i.'</a> ';
			if ($i==$pager['page']) $pager['head'] .= '</b>';
		}

		if ($pager['page'] < $pager['tot_pages']) {
			$pager['head'] .= '<a href="'.URLadd('p', $pager['page']+1, $_add_value).'">';
			$pager['head'] .= '<img src="'.$config['core_web_root'].'gfx/arrow_next.png" alt="Next" width="11" height="12"/></a>';
		//} else {
		//	$pager['head'] .= '<img src="'.$config['core_web_root'].'gfx/arrow_next_gray.png" alt="" width="11" height="12"/>';
		}
		
		$pager['head'] .= '<br/>';

		return $pager;
	}

?>