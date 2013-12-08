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
		<legend><?php echo JText::_('RSFP_USER_EMAILS'); ?></legend>
		<table width="100%" class="com-rsform-table-props">
			<tr>
				<td colspan="2"><div class="rsform_error"><?php echo JText::_('RSFP_EMAILS_DESC'); ?></div></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><span style="color: red"><?php echo JText::_('RSFP_EMAILS_FROM'); ?></span></td>
				<td>
					<input name="UserEmailFrom" class="rs_inp rs_80" id="UserEmailFrom" value="<?php echo $this->escape($this->form->UserEmailFrom); ?>" size="35" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" />
				</td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo RSFormProHelper::translateIcon(); ?>  <span style="color: red"><?php echo JText::_('RSFP_EMAILS_FROM_NAME'); ?></td>
				<td>
					<input name="UserEmailFromName" class="rs_inp rs_80" id="UserEmailFromName" value="<?php echo $this->escape($this->form->UserEmailFromName); ?>" size="35" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" />
				</td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_REPLY_TO'); ?></td>
				<td>
					<input name="UserEmailReplyTo" class="rs_inp rs_80" id="UserEmailReplyTo" value="<?php echo $this->escape($this->form->UserEmailReplyTo); ?>" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" />
				</td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><span style="color: red"><?php echo JText::_('RSFP_EMAILS_TO'); ?></span></td>
				<td>
					<input name="UserEmailTo" class="rs_inp rs_80" id="UserEmailTo" value="<?php echo $this->escape($this->form->UserEmailTo); ?>" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" />
				</td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_CC'); ?></td>
				<td><input name="UserEmailCC" class="rs_inp rs_80" id="UserEmailCC" value="<?php echo $this->escape($this->form->UserEmailCC); ?>" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_BCC'); ?></td>
				<td><input name="UserEmailBCC" class="rs_inp rs_80" id="UserEmailBCC" value="<?php echo $this->escape($this->form->UserEmailBCC); ?>" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo RSFormProHelper::translateIcon(); ?>  <span style="color: red"><?php echo JText::_('RSFP_EMAILS_SUBJECT'); ?></span></td>
				<td><input name="UserEmailSubject" class="rs_inp rs_80" id="UserEmailSubject" value="<?php echo $this->escape($this->form->UserEmailSubject); ?>" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_ATTACH_FILE'); ?></td>
				<td><?php echo $this->lists['UserEmailAttach'];?></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_ATTACH_FILE_LOCATION'); ?></td>
				<td>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%;">
				<tr>
					<td>
						<input name="UserEmailAttachFile" class="rs_inp rs_80" id="UserEmailAttachFile" value="<?php echo !empty($this->form->UserEmailAttachFile) ? $this->form->UserEmailAttachFile : JPATH_SITE.'/components/com_rsform/uploads'; ?>" <?php if (!$this->form->UserEmailAttach) { ?>disabled="disabled"<?php } ?> />
					</td>
					<td width="1%" nowrap="nowrap">
						<a href="index.php?option=com_rsform&amp;controller=files&amp;task=display&amp;folder=<?php echo @dirname($this->form->UserEmailAttachFile); ?>&amp;tmpl=component" class="rs_button rs_left modal" rel="{handler: 'iframe'}" id="rsform_select_file" <?php if (!$this->form->UserEmailAttach) { ?>style="display: none"<?php } ?>><span class="rsform_icon rsform_upload "><?php echo JText::_('RSFP_SELECT_FILE'); ?></span></a>
					</td>
				</tr>
				<?php if ($this->form->UserEmailAttach && (!file_exists($this->form->UserEmailAttachFile) || !is_file($this->form->UserEmailAttachFile))) { ?>
				<tr>
					<td colspan="2"><strong style="color: red"><?php echo JText::_('RSFP_EMAILS_ATTACH_FILE_WARNING'); ?></strong></td>
				</tr>
				<?php } ?>
				</table>
				</td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_MODE'); ?></td>
				<td><?php echo $this->lists['UserEmailMode']; ?></td>
			</tr>
			<tr>
				<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo RSFormProHelper::translateIcon(); ?>  <span style="color: red"><?php echo JText::_('RSFP_EMAILS_TEXT'); ?></span></td>
				<td>
					<button class="rs_button rs_left" id="rsform_edit_user_email" onclick="openRSModal('<?php echo JRoute::_('index.php?option=com_rsform&task=richtext.show&opener=UserEmailText&formId='.$this->form->FormId.'&tmpl=component'.(!$this->form->UserEmailMode ? '&noEditor=1' : '')); ?>')" type="button"><span class="rsform_edit"><?php echo JText::_('RSFP_EMAILS_EDIT_TEXT'); ?></span></button>
					<button class="rs_button rs_left" onclick="openRSModal('<?php echo JRoute::_('index.php?option=com_rsform&task=richtext.preview&opener=UserEmailText&formId='.$this->form->FormId.'&tmpl=component'); ?>', 'RichtextPreview')" type="button"><span class="rsform_preview"><?php echo JText::_('PREVIEW'); ?></span></button>
				</td>
			</tr>
		</table>
		</fieldset>
		<?php $this->triggerEvent('rsfp_bk_onAfterShowUserEmail'); ?>
	</td>
	<td valign="top" width="1%" nowrap="nowrap">
		<button class="rs_button" type="button" onclick="toggleQuickAdd();"><?php echo JText::_('RSFP_TOGGLE_QUICKADD'); ?></button>
			<div id="QuickAdd3">
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