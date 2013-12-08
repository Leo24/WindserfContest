<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<?php if ($this->params->get('show_page_heading', 0)) { ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php } ?>
<?php echo RSFormProHelper::displayForm($this->formId); ?>