<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

echo JHtml::_('jgrid.state', array(
	0 => array('setrequired', 'JYES', '', '', false, 'unpublish', 'unpublish'),
	1 => array('unsetrequired', 'JNO', '', '', false, 'publish', 'publish')
), $this->field->required, $this->i, 'components.');