<?php
/*------------------------------------------------------------------------
# mod_responsivegallery - Responsive Photo Gallery for Joomla 1.5 & 2.5 v2.0.0
# ------------------------------------------------------------------------
# author    GraphicAholic
# copyright Copyright (C) 2011 GraphicAholic.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.graphicaholic.com
-------------------------------------------------------------------------*/
// No direct access
defined('_JEXEC') or die('Restricted access');
$LiveSite 	= JURI::base();
$document =& JFactory::getDocument();
$modbase = JURI::base(true).'/modules/mod_responsivegallery/';
$thumbPosition = $params->get('thumbPosition', '1');
$styles = $params->get('styles', '1');
$itemTitle = $params->get('itemTitle',1);
$moduleID = $module->id;
JHTML::_(' behavior.mootools');
if ($params->get('load_jquery') == 1) {
	$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
}
if ($thumbPosition == "2") {
	$document->addScript($modbase.'js/gallery_below.js');
}
else {
	$document->addScript($modbase.'js/gallery.js');
}
if ($styles == "2") {
	$document->addStyleSheet($modbase.'css/style_light.css');
	$document->addStyleSheet($modbase.'css/elastislide_light.css');
}
else {
	$document->addStyleSheet($modbase.'css/style_dark.css');
	$document->addStyleSheet($modbase.'css/elastislide_dark.css');
}
$document->addScript ($modbase.'js/jquery.tmpl.js');
$document->addScript ($modbase.'js/jquery.easing.1.3.js');
$document->addScript ($modbase.'js/jquery.elastislide.js');
$document->addStyleSheet($modbase.'css/demo.css');
$moduleId 	 	= $module->id;

require_once (dirname(__FILE__).DS.'helper.php');
$list = modResponsiveGalleryHelper::getimgList($params, $moduleID);
require(JModuleHelper::getLayoutPath('mod_responsivegallery'));
?>