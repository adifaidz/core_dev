<?php
/**
 * XHTML anchor tag
 *
 * @author Martin Lindhe, 2007-2014 <martin@ubique.se>
 */

//STATUS: wip

namespace cd;

class XhtmlComponentA extends XhtmlComponent
{
    var $content;   ///< content of the anchor tag
    var $href;
    var $rel;
    var $target;
    var $class;
    var $style;
    var $onclick;

    function onClick($js) { $this->onclick = $js; }

    function render()
    {
        return
        '<a href="'.$this->href.'"'.
        ($this->rel     ? ' rel="'.$this->rel.'"' : '').
        ($this->target  ? ' target="'.$this->target.'"' : '').
        ($this->class   ? ' class="'.$this->class.'"' : '').
        ($this->style   ? ' style="'.$this->style.'"' : '').
        ($this->onclick ? ' onclick="'.$this->onclick.'"' : '').
        '>'.$this->content.'</a>';
    }

}
