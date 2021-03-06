<?php
/**
 * XHTML button
 *
 * @author Martin Lindhe, 2011-2014 <martin@ubique.se>
 */

//STATUS: wip

namespace cd;

class XhtmlComponentButton extends XhtmlComponent
{
    var $text = 'Click me';
    var $class = 'button';
    var $style;
    var $onclick;

    function onClick($js) { $this->onclick = $js; }

    function render()
    {
        return
        '<button type="button"'.
        ($this->class ? ' class="'.$this->class.'"' : '').
        ($this->style ? ' style="'.$this->style.'"' : '').
        ($this->onclick ? ' onclick="'.$this->onclick.'"' : '').
        '>'.$this->text.'</button>';
    }

}
