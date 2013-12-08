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

$relName = 'lightbox';

$mtVersion = (version_compare(JVERSION,'1.6.0','ge') || JPluginHelper::isEnabled('system','mtupgrade')) ? 'slimbox-1.71a' : 'slimbox-1.58';

$stylesheets = array($mtVersion.'/css/slimbox.css');
$stylesheetDeclarations = array();
$scripts = array($mtVersion.'/js/slimbox.js');
$scriptDeclarations = array();
