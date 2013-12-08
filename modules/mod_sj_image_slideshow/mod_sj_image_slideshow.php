<?php
/**
 * @package Sj Image Slideshow
 * @version 1.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 */

defined('_JEXEC') or die;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
	
require_once dirname( __FILE__ ).'/core/helper.php';

$layout = $params->get('layout', 'default');
$cacheid = md5(serialize(array ($layout, $module->id)));
$cacheparams = new stdClass;
$cacheparams->cachemode = 'id';
$cacheparams->class = 'ImageSlideShow';
$cacheparams->method = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams = $cacheid;
$list = JModuleHelper::moduleCache ($module, $params, $cacheparams);
if(!empty($list)) {
	$start = $params->get('startingSlide', 1);
	if ($start <= 0 || ($start > count($list))){
		$start = 0;
	}else{		
	    $start = $start - 1;
	}
	$progress = $params->get('progress',1);
	$play = $params->get('play', 1);
	$fx = ($params->get('effect') != 'random')?$params->get('effect'):'random';
	$effect = array( 0=>'tileBlind', 1=>'tileSlide' );
	require JModuleHelper::getLayoutPath($module->module, $layout);
} else {
	echo JText::_('Has no content to show!');
}