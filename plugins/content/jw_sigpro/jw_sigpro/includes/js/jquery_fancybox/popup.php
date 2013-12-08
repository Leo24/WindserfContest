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

$relName = 'fancybox';
$extraClass = ' fancybox';

$stylesheets = array('fancybox/jquery.fancybox-1.3.4.css');
$stylesheetDeclarations = array();
$scripts = array(
	'http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js',
	'fancybox/jquery.mousewheel-3.0.4.pack.js',
	'fancybox/jquery.fancybox-1.3.4.pack.js'
);
$scriptDeclarations = array('
	//<![CDATA[
	jQuery.noConflict();
	jQuery(function($) {
		$("a.fancybox").fancybox({
				\'transitionIn\'	: \'none\', // elastic | none
				\'transitionOut\'	: \'none\', // elastic | none
				\'titlePosition\' : \'inside\', // inside | over
				\'titleFormat\'		: function(title, currentArray, currentIndex, currentOpts) {
					return \'<span id="fancybox-title-over"><b>Image \' + (currentIndex + 1) + \' of \' + currentArray.length + \'</b><br />\' + (title.length ? title : \'\') + \'</span>\';
				}		
		});
	});
	//]]>
');
