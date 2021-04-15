<?php
/**
* @title			Mx Swiper
* @version   		4.0.0
* @copyright   		Copyright (C) 2020 mixwebtemplates.com, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   	http://www.mixwebtemplates.com/
* @developers   	mixwebtemplates.com
*/

// no direct access
defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');
class JFormFieldSeparator extends JFormField {
	protected $type = 'Separator';
	protected function getInput() {
		$text  	= (string) $this->element['text'];
		return '<div id="'.$this->id.'" class="mmSeparator'.(($text != '') ? ' hasText' : '').'" title="'. JText::_($this->element['desc']) .'"><span>' . JText::_($text) . '</span></div>';
	}
}
/* EOF */