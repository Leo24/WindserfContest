<?php
/**
 * @version		2.5.3
 * @package		Simple Image Gallery Pro
 * @author    JoomlaWorks - http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		Commercial - This code cannot be redistributed without permission from JoomlaWorks Ltd.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$relName = 'gb_imageset';

$stylesheets = array('gb_styles.css');
$stylesheetDeclarations = array();
$scripts = array('AJS.js','AJS_fx.js');
$scriptDeclarations = array('
	//<![CDATA[
	var GB_ROOT_DIR = "'.$popupPath.'/";
	//]]>
');

$legacyHeadIncludes = '<script type="text/javascript" src="'.$popupPath.'/gb_scripts.js"></script>';
