<?php
/**
 * XHTML password input field
 *
 * @author Martin Lindhe, 2007-2014 <martin@ubique.se>
 */

//STATUS: wip

namespace cd;

class XhtmlComponentPassword extends XhtmlComponent
{
    var $id;                ///< xhtml component id
    var $value;             ///< default value
    var $size;              ///<
    var $width;             ///< width in pixels
    var $maxlen;            ///<
    var $disabled = false;  ///< disable field (make it read only)
    var $onchange;          ///< js to execute on change event (when input field lost focus after user input)

    function onChange($s) { $this->onchange = $s; }

    function render()
    {
        $id = $this->id ? $this->id : $this->name;
        if (strpos($id, '[') !== false && strpos($id, ']') !== false)
            $id = '';

        if (!is_alphanumeric($id))
            throw new \Exception ('no: '.$id );

        $res =
        '<input type="password"'.
        ' name="'.$this->name.'"'.
        ($id ? ' id="'.$id.'"' : '').
        (($this->value || is_string($this->value)) ? ' value="'.$this->value.'"' : '').
        ($this->size ? ' size="'.$this->size.'"': '').
        ($this->maxlen ? ' maxlength="'.$this->maxlen.'"': '').
        ($this->disabled ? ' disabled': '').
        ($this->onchange ? ' onchange="'.$this->onchange.'"': '').
        ($this->width ? ' style="width:'.$this->width.'px"': '').
        '/>';

        return $res;
    }

}
