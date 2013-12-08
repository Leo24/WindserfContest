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
	class JFormFieldPositions extends JFormField {
		var	$type = 'positions';
		function getInput(){
			return JElementPositions::fetchElement($this->name, $this->value, $this->element, $this->options['control']);
		}
	}
}

jimport('joomla.html.parameter.element');

class JElementPositions extends JElement {

	var	$_name = 'positions';

	function fetchElement($name, $value, &$node, $control_name){
		$db	=& JFactory::getDBO();
		$query = 'SELECT DISTINCT(template) AS text, template AS value FROM #__templates_menu WHERE client_id = 0';
		$db->setQuery( $query );
		$templates = $db->loadObjectList();
		$query = 'SELECT DISTINCT(position) FROM #__modules WHERE client_id = 0';
		$db->setQuery( $query );
		$positions = $db->loadResultArray();
		$positions = (is_array($positions)) ? $positions : array();
		
		for ($i = 0, $n = count($templates); $i < $n; $i++ ){
			$path = JPATH_SITE.DS.'templates'.DS.$templates[$i]->value;
			$xml =& JFactory::getXMLParser('Simple');
			if ($xml->loadFile($path.DS.'templateDetails.xml')){
				$p =& $xml->document->getElementByPath('positions');
				if (is_a($p, 'JSimpleXMLElement') && count($p->children())){
					foreach ($p->children() as $child){
						if (!in_array($child->data(), $positions)) $positions[] = $child->data();
					}
				}
			}
		}

		if(defined('_JLEGACY') && _JLEGACY == '1.0'){
			$positions[] = 'left';
			$positions[] = 'right';
			$positions[] = 'top';
			$positions[] = 'bottom';
			$positions[] = 'inset';
			$positions[] = 'banner';
			$positions[] = 'header';
			$positions[] = 'footer';
			$positions[] = 'newsflash';
			$positions[] = 'legals';
			$positions[] = 'pathway';
			$positions[] = 'breadcrumb';
			$positions[] = 'user1';
			$positions[] = 'user2';
			$positions[] = 'user3';
			$positions[] = 'user4';
			$positions[] = 'user5';
			$positions[] = 'user6';
			$positions[] = 'user7';
			$positions[] = 'user8';
			$positions[] = 'user9';
			$positions[] = 'advert1';
			$positions[] = 'advert2';
			$positions[] = 'advert3';
			$positions[] = 'debug';
			$positions[] = 'syndicate';
		}

		$positions = array_unique($positions);
		sort($positions);

		$options[] = JHTML::_('select.option','',JText::_('JW_SIGP_XML___NONE_SELECTED__'), 'id', 'title');
		foreach ($positions as $position){
			$options[] = JHTML::_('select.option',$position,$position, 'id', 'title');
		}

		$output = JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.'][]', 'class="inputbox"', 'id', 'title', $value, $control_name.$name );

		return $output;
		
	}
}
