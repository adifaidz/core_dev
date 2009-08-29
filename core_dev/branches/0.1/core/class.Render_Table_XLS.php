<?php
/**
 * $Id$
 *
 * Renders a table of data in Microsoft Excel Spreadsheet (.xls) format
 *
 * The output file has been tested successfully with:
 *
 *		Microsoft Office 2003
 *		Open Office 2.4
 *
 * @author Martin Lindhe, 2008 <martin@startwars.org>
 */

//FIXME try Office 2007

require_once('output_xls.php');

class Render_Table_XLS extends Render_Table
{
	protected $default_ext = '.xls';

	function render()
	{
		$out = xlsBOF();

		$row = 0;
		$col = 0;

		if ($this->heads) {
			foreach ($this->heads as $h) {
				$out .= xlsWriteText($row, $col, $h);	//FIXME can text be made bold or something?
				$col++;
			}
			$row = 1;
			$col = 0;
		}

		foreach ($this->data as $d) {
			if (is_numeric($d)) {
				$out .= xlsWriteNumber($row, $col, $d);
			} else {
				$out .= xlsWriteText($row, $col, $d);
			}
			$col++;
			if ($col == $this->columns) {
				$row++;
				$col = 0;
			}
		}

		$out .= xlsEOF();
		return $out;
	}
}

?>