<?php
/**
* @version 1.3.0
* @package RSform!Pro 1.3.0
* @copyright (C) 2007-2010 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<table class="admintable">
<tr>
	<td valign="top" align="left" width="30%">
		<table>
			<tr>
				<td colspan="2"><div class="rsform_error"><?php echo JText::_('RSFP_REG_DESC'); ?></div></td>
			</tr>
			<tr>
				<td width="80" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_REG_USE_INTEGRATION'); ?></td>
				<td><?php echo $lists['published']; ?></td>
			</tr>
			<tr>
				<td width="80" align="right" nowrap="nowrap" class="key"><span class="hasTip" title="<?php echo RSFormProHelper::isJ16() ? JText::_('RSFP_REG_ACTIVATION_25_DESC') : JText::_('RSFP_REG_ACTIVATION_DESC'); ?>"><?php echo JText::_('RSFP_REG_ACTIVATION'); ?></span></td>
				<td><?php echo $lists['activation']; ?></td>
			</tr>
			<?php if ($cb) { ?>
			<tr>
				<td width="80" align="right" nowrap="nowrap" class="key"><span class="hasTip" title="<?php echo JText::_('RSFP_REG_ACTIVATION_CB_DESC'); ?>"><?php echo JText::_('RSFP_REG_ACTIVATION_CB'); ?></span></td>
				<td><?php echo $lists['cb']; ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="2" class="key" align="center"><p align="center"><?php echo JText::_('RSFP_REG_MERGE_VARS'); ?></p></td>
			</tr>
			<tr>
				<td colspan="2"><?php echo JText::_('RSFP_REG_MERGE_VARS_DESC'); ?></td>
			</tr>
			<?php if (is_array($merge_vars)) { ?>
				<?php foreach ($merge_vars as $merge_var => $title) { ?>
				<tr>
					<td nowrap="nowrap" align="right"><?php echo $title; ?></td>
					<td><?php echo $lists['fields'][$merge_var]; ?></td>
				</tr>
				<?php } ?>
			<?php } ?>
		</table>
	</td>
	<td valign="top">
		&nbsp;
	</td>
</tr>
</table>