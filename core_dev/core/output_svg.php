<?php
/**
 * $Id$
 *
 * Simple SVG renderer
 * Currently only capable of rendering a set of polygons
 *
 * Documentation:
 * http://www.w3.org/TR/SVG11/
 * http://www.w3.org/Graphics/SVG/
 * http://www.w3.org/TR/SVG/shapes.html
 *
 * SVG test suite:
 * http://www.w3.org/Graphics/SVG/Test/
 *
 * @author Martin Lindhe, 2008 <martin@startwars.org>
 */

//TODO: set background color
//TODO: define transpacency on polygons

class svg
{
	var $polygons = array();
	var $width, $height;

	function __construct($width = 100, $height = 100)
	{
		$this->width = $width;
		$this->height = $height;
	}

	function addPoly($poly)
	{
		$this->polygons[] = $poly;
	}

	function addPolys($list)
	{
		if (!is_array($list)) return false;

		foreach ($list as $poly) {
			$this->polygons[] = $poly;
		}
	}

	function render()
	{
		$res =
		'<?xml version="1.0" encoding="UTF-8"?>'.
		'<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'.
		'<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"'.
			' version="1.1" width="'.$this->width.'px" height="'.$this->height.'px" viewBox="0 0 '.$this->width.' '.$this->height.'">';

		foreach ($this->polygons as $poly) {
			$res .=
			'<polygon stroke-width="1"'.
				' fill="#'.sprintf('%06x', $poly['color']).'"'.
				' stroke="#'.sprintf('%06x', $poly['border']).'"'.
				' points="';
			for ($i=0; $i<count($poly['coords']); $i+=2) {
				$res .= $poly['coords'][$i].','.$poly['coords'][$i+1];
				if ($i < count($poly['coords'])-2) $res .= ',';
			}
			$res .=
			'"/>';
		}

		$res .=
		'</svg>';

		return $res;
	}

	function output()
	{
		header('Content-type: image/svg+xml');

		echo $this->render();
	}

	function save($filename)
	{
		file_put_contents($filename, $this->render());
	}
}

?>
