<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<table class="admintable">
<tr>
	<td valign="top" align="left">
		<fieldset>
		<legend><?php echo JText::_('RSFP_ADMIN_EMAILS'); ?></legend>
		<table width="100%" class="com-rsform-table-props">
			<tr>
				<td colspan="2"><div class="rsform_error"><?php echo JText::_('RSFP_EMAILS_DESC'); ?></div></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><span style="color: red"><?php echo JText::_('RSFP_EMAILS_FROM'); ?></span></td>
				<td>
					<input name="AdminEmailFrom" class="rs_inp rs_80" id="AdminEmailFrom" value="<?php echo $this->escape($this->form->AdminEmailFrom); ?>" size="35" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" />
				</td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo RSFormProHelper::translateIcon(); ?>  <span style="color: red"><?php echo JText::_('RSFP_EMAILS_FROM_NAME'); ?></td>
				<td>
					<input name="AdminEmailFromName" class="rs_inp rs_80" id="AdminEmailFromName" value="<?php echo $this->escape($this->form->AdminEmailFromName); ?>" size="35" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" />
				</td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_REPLY_TO'); ?></td>
				<td><input name="AdminEmailReplyTo" class="rs_inp rs_80" id="AdminEmailReplyTo" value="<?php echo $this->escape($this->form->AdminEmailReplyTo); ?>" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><span style="color: red"><?php echo JText::_('RSFP_EMAILS_TO'); ?></span></td>
				<td><input name="AdminEmailTo" class="rs_inp rs_80" id="AdminEmailTo" value="<?php echo $this->escape($this->form->AdminEmailTo); ?>" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_CC'); ?></td>
				<td><input name="AdminEmailCC" class="rs_inp rs_80" id="AdminEmailCC" value="<?php echo $this->escape($this->form->AdminEmailCC); ?>" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_BCC'); ?></td>
				<td><input name="AdminEmailBCC" class="rs_inp rs_80" id="AdminEmailBCC" value="<?php echo $this->escape($this->form->AdminEmailBCC); ?>" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo RSFormProHelper::translateIcon(); ?>  <span style="color: red"><?php echo JText::_('RSFP_EMAILS_SUBJECT'); ?></span></td>
				<td><input name="AdminEmailSubject" class="rs_inp rs_80" id="AdminEmailSubject" value="<?php echo $this->escape($this->form->AdminEmailSubject); ?>" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_MODE'); ?></td>
				<td><?php echo $this->lists['AdminEmailMode'];?></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo RSFormProHelper::translateIcon(); ?>  <span style="color: red"><?php echo JText::_('RSFP_EMAILS_TEXT'); ?></span></td>
				<td>
					<button class="rs_button rs_left" id="rsform_edit_admin_email" onclick="openRSModal('<?php echo JRoute::_('index.php?option=com_rsform&task=richtext.show&opener=AdminEmailText&formId='.$this->form->FormId.'&tmpl=component'.(!$this->form->AdminEmailMode ? '&noEditor=1' : '')); ?>')" type="button"><span class="rsform_edit"><?php echo JText::_('RSFP_EMAILS_EDIT_TEXT'); ?></span></button>
					<button class="rs_button rs_left" onclick="openRSModal('<?php echo JRoute::_('index.php?option=com_rsform&task=richtext.preview&opener=AdminEmailText&formId='.$this->form->FormId.'&tmpl=component'); ?>', 'RichtextPreview')" type="button"><span class="rsform_preview"><?php echo JText::_('PREVIEW'); ?></span></button>
				</td>
			</tr>
		</table>
		</fieldset>
		<?php $this->triggerEvent('rsfp_bk_onAfterShowAdminEmail'); ?>
	</td>
	<td valign="top" width="1%" nowrap="nowrap">
		<button class="rs_button" type="button" onclick="toggleQuickAdd();"><?php echo JText::_('RSFP_TOGGLE_QUICKADD'); ?></button>
			<div id="QuickAdd4">
			<h3><?php echo JText::_('RSFP_QUICK_ADD');?></h3>
				<?php echo JText::_('RSFP_QUICK_ADD_DESC');?><br/><br/>
			<?php if(!empty($this->quickfields))
				foreach($this->quickfields as $quickfield) { ?>
					<strong><?php echo $quickfield;?></strong><br/>
					<pre>{<?php echo $quickfield; ?>:caption}</pre>
					<pre>{<?php echo $quickfield; ?>:value}</pre>
					<br/>
			<?php } ?>
		</div>
	</td>
</tr>
</table>