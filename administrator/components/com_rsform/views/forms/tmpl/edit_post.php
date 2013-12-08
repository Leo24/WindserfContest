<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<fieldset>
<legend><?php echo JText::_('RSFP_POST_TO_LOCATION'); ?></legend>
<table width="100%" class="com-rsform-table-props">
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_POST_ENABLED'); ?></td>
		<td><?php echo $this->lists['post_enabled']; ?></td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_POST_SILENT'); ?></td>
		<td><?php echo $this->lists['post_silent']; ?></td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_POST_METHOD'); ?></td>
		<td><?php echo $this->lists['post_method']; ?></td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_POST_URL'); ?></td>
		<td><input class="rs_inp rs_80" name="form_post[url]" value="<?php echo $this->escape($this->form_post->url); ?>" size="105" /></td>
	</tr>
</table>
</fieldset>