<?php
/*
# ------------------------------------------------------------------------
# Templates for Joomla 2.5 - Joomla 3.5
# ------------------------------------------------------------------------
# Copyright (C) 2011-2013 Jtemplate.ru. All Rights Reserved.
# @license - PHP files are GNU/GPL V2.
# Author: Makeev Vladimir
# Websites:  http://www.jtemplate.ru 
# ---------  http://code.google.com/p/jtemplate/   
# ------------------------------------------------------------------------
*/

// No direct access
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$script_vk			= (int)$params->get('script_vk');
$group_id 			= $params->get('group_id');
$width 				= (int)$params->get('width');
$height				= (int)$params->get('height');
$mode 				= (int)$params->get('mode');
$moduleclass_sfx	= $params->get('moduleclass_sfx');

if ( $script_vk == 0 ) {	
	$document->addCustomTag('<script src="http://vkontakte.ru/js/api/openapi.js" type="text/javascript" charset="utf-8"></script>');
}
require JModuleHelper::getLayoutPath('mod_jt_vk_group', $params->get('layout', 'default'));
echo JText::_(MOD_JT);
?>
