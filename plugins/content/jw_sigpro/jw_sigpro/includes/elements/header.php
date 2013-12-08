<?php
/**
 * @version		2.5.3
 * @package		Simple Image Gallery Pro
 * @author    JoomlaWorks - http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		Commercial - This code cannot be redistributed without permission from JoomlaWorks Ltd.
 */

if(version_compare(JVERSION,'1.6.0','ge')) {
	jimport('joomla.form.formfield');
	class JFormFieldHeader extends JFormField {
		var	$type = 'header';
		function getInput(){
			return JElementHeader::fetchElement($this->name, $this->value, $this->element, $this->options['control']);
		}
	}
}

jimport('joomla.html.parameter.element');

class JElementHeader extends JElement {
	var	$_name = 'header';
	
	function fetchElement($name, $value, &$node, $control_name){
		// Output
		return '
		<div style="clear:both;font-weight:normal;font-size:14px;color:#fff;padding:4px;margin:0;background:#0B55C4;">
			'.JText::_($value).'
		</div>
		';
	}
	
}
