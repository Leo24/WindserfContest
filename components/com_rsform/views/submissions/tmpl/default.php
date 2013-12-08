<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<?php if (RSFormProHelper::isJ16()) { ?>
	<?php if ($this->params->get('show_page_heading', 1)) { ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php } ?>
<?php } else { ?>
	<?php if ($this->params->get('show_page_title', 1)) { ?>
	<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo $this->escape($this->params->get('page_title')); ?></div>
	<?php } ?>
<?php } ?>

<?php if ($this->params->get('show_search',0)) { ?>
<form method="post" action="<?php echo JRoute::_('index.php?option=com_rsform&view=submissions'.$this->itemid, false); ?>" name="adminForm" id="adminForm">
<div>
	<?php echo JText::_('RSFP_SEARCH'); ?> <input type="text" id="rsfilter" name="filter" value="<?php echo $this->escape($this->filter); ?>" onchange="document.adminForm.submit();" /> 
	<button type="button" onclick="document.adminForm.submit();"><?php echo JText::_('RSFP_GO'); ?></button> 
	<button type="button" onclick="document.getElementById('rsfilter').value='';document.adminForm.submit();"><?php echo JText::_('RSFP_RESET'); ?></button>
</div>
<?php } ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td><?php echo $this->template; ?></td>
	</tr>
	<tr>
		<td align="center" class="sectiontablefooter<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
			<div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
		</td>
	</tr>
	<tr>
		<td align="center"><?php echo $this->pagination->getPagesCounter(); ?></td>
	</tr>
</table>

<?php if ($this->params->get('show_search',0)) { ?>
</form>
<?php } ?>