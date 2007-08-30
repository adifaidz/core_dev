<?
	//returns a $pager array with some properties filled
	//reads the 'p' get parameter for current page
	//example: $pager = makePager(102, 25);		will create a pager for total of 102 items with 25 items per page
	
	//$pager['limit'] är för-genererad LIMIT sql för att använda tillsammans med query som hämtar en del av en lista
	//  alternativt kan $pager['index'] och $pager['items_per_page'] användas för samma syfte
	function makePager($_total_cnt, $_items_per_page, $_add_value = '')
	{
		$pager['page'] = 1;
		$pager['items_per_page'] = $_items_per_page;
		if (!empty($_GET['p']) && is_numeric($_GET['p'])) $pager['page'] = $_GET['p'];

		$pager['tot_pages'] = round($_total_cnt / $_items_per_page+0.4); // round to closest whole number
		$pager['head'] = 'Sida '.$pager['page'].' av '.$pager['tot_pages'].' (tot. '.$_total_cnt.')<br/><br/>';

		$pager['index'] = ($pager['page']-1) * $pager['items_per_page'];
		$pager['limit'] = ' LIMIT '.$pager['index'].','.$pager['items_per_page'];

		if ($pager['tot_pages'] <= 1) return $pager;

		if ($pager['page'] > 1) {
			$pager['head'] .= '<a href="'.URLadd('p', $pager['page']-1, $_add_value).'">';
			$pager['head'] .= '<img src="gfx/arrow_prev.png" alt="Previous" width="11" height="12"/></a>';
		} else {
			//$pager['head'] .= '<img src="/gfx/arrow_prev_gray.png" alt="" width="11" height="12"/>';
		}

		for ($i=1; $i <= $pager['tot_pages']; $i++) {
			if ($i==$pager['page']) $pager['head'] .= '<b>';
			$pager['head'] .= ' <a href="'.URLadd('p', $i, $_add_value).'">'.$i.'</a> ';
			if ($i==$pager['page']) $pager['head'] .= '</b>';
		}

		if ($pager['page'] < $pager['tot_pages']) {
			$pager['head'] .= '<a href="'.URLadd('p', $pager['page']+1, $_add_value).'">';
			$pager['head'] .= '<img src="gfx/arrow_next.png" alt="Next" width="11" height="12"/></a>';
		} else {
			//$pager['head'] .= '<img src="/gfx/arrow_next_gray.png" alt="" width="11" height="12"/>';
		}

		return $pager;
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

?>
