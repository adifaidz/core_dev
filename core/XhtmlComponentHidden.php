<?php
/**
 * XHTML hidden input field
 *
 * @author Martin Lindhe, 2007-2014 <martin@ubique.se>
 */

//STATUS: wip

namespace cd;

class XhtmlComponentHidden extends XhtmlComponent
{
    var $value; ///<  field value   (XXXX or array of multiple values ???)

    function render()
    {
        if (is_array($this->value))
            throw new \Exception ('dont use arrays'); //XXX do any code exploit this "feature"?

/*
        if (is_array($this->value))
            foreach ($this->value as $v)
                $out .= '<input type="hidden" name="'.$this->name.'[]" value="'.$v.'"/>';
*/
        return
        '<input type="hidden"'.
        ' name="'.$this->name.'"'.
        ' value="'.htmlspecialchars($this->value).'"'.
        '/>';
    }

}
