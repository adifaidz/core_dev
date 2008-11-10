<?php
/**
 * $Id$
 *
 * General functions for Microsoft Excel Spreadsheet (.xls) format
 *
 * Parts of the code was based on http://px.sklar.com/code.html/id=488 by Christian Novak
 *
 * \author Martin Lindhe, 2008 <martin@startwars.org>
 */


/** Beginning Of File marker */
function xlsBOF()
{
	return pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
}

/** End Of File marker */
function xlsEOF()
{
	return pack("ss", 0x0A, 0x00);
}

/** Writes a number */
function xlsWriteNumber($row, $col, $val)
{
	//FIXME this don't output good enough precision, seem to round to 2 decimals (at least Open Office)

	return pack("sssssd", 0x203, 14, $row, $col, 0x0, $val);	//0x203 = double
}

/** Writes a text string */
function xlsWriteText($row, $col, $text)
{
	//FIXME support unicode strings, see pg 18 in Excel97-2007BinaryFileFormat(xls)Specification.xps
	$len = strlen($text);
	return pack("ssssss", 0x204, 8 + $len, $row, $col, 0x0, $len) . $text;	//0x204 = label
}

?>