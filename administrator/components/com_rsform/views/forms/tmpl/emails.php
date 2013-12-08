<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<form method="post" action="index.php?option=com_rsform" name="adminForm" id="adminForm">
<p>
	<button class="rs_button rs_left" type="button" onclick="submitform('emails.apply');"><?php echo JText::_('APPLY'); ?></button>
	<button class="rs_button rs_left" type="button" onclick="submitform('emails.save');"><?php echo JText::_('SAVE'); ?></button>
	<button class="rs_button rs_left" type="button" onclick="window.close();"><?php echo JText::_('CLOSE'); ?></button>
</p>
<span class="rsform_clear_both"></span>
<fieldset class="form-horizontal">
<legend><?php echo JText::_('RSFP_FORM_EMAILS_NEW'); ?></legend>
<table width="100%" class="com-rsform-table-props">
	<tr>
		<td colspan="2"><?php if ($this->row->id) { ?><span><?php echo $this->lists['Languages']; ?></span><span><?php echo JText::sprintf('RSFP_YOU_ARE_EDITING_IN', $this->lang, RSFormProHelper::translateIcon()); ?></span><?php } ?></td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><span style="color: red"><?php echo JText::_('RSFP_EMAILS_FROM'); ?></span></td>
		<td>
			<input name="from" class="rs_inp rs_80" id="from" value="<?php echo $this->escape($this->row->from); ?>" size="35" style="width:500px;" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" />
		</td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo RSFormProHelper::translateIcon(); ?>  <span style="color: red"><?php echo JText::_('RSFP_EMAILS_FROM_NAME'); ?></td>
		<td>
			<input name="fromname" class="rs_inp rs_80" id="fromname" value="<?php echo $this->escape($this->row->fromname); ?>" size="35" style="width:500px;" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" />
		</td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_REPLY_TO'); ?>:</td>
		<td><input name="replyto" class="rs_inp rs_80" id="replyto" value="<?php echo $this->escape($this->row->replyto); ?>" style="width:500px;" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><span style="color: red"><?php echo JText::_('RSFP_EMAILS_TO'); ?></span></td>
		<td><input name="to" class="rs_inp rs_80" id="to" value="<?php echo $this->escape($this->row->to); ?>" style="width:500px;" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_CC'); ?></td>
		<td><input name="cc" class="rs_inp rs_80" id="cc" value="<?php echo $this->escape($this->row->cc); ?>" style="width:500px;" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_BCC'); ?></td>
		<td><input name="bcc" class="rs_inp rs_80" id="bcc" value="<?php echo $this->escape($this->row->bcc); ?>" style="width:500px;" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo RSFormProHelper::translateIcon(); ?>  <span style="color: red"><?php echo JText::_('RSFP_EMAILS_SUBJECT'); ?></span></td>
		<td><input name="subject" class="rs_inp rs_80" id="subject" value="<?php echo $this->escape($this->row->subject); ?>" style="width:500px;" onkeydown="closeAllDropdowns();" onclick="toggleDropdown(this);" /></td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo JText::_('RSFP_EMAILS_MODE'); ?></td>
		<td class="com-rsform-css-fix"><?php echo $this->lists['mode'];?></td>
	</tr>
	<tr>
		<td width="25%" align="right" nowrap="nowrap" class="key"><?php echo RSFormProHelper::translateIcon(); ?>  <span style="color: red"><?php echo JText::_('RSFP_EMAILS_TEXT'); ?></span></td>
		<td>
			<?php if ($this->row->mode) { ?>
			<?php echo $this->editor->display('message', $this->escape($this->row->message), 500, 320, 70, 10); ?>
			<?php } else { ?>
			<textarea id="message" name="message" style="width: 500px; height: 320px;" rows="10" cols="70"><?php echo $this->escape($this->row->message); ?></textarea>
			<?php } ?>
		</td>
	</tr>
</table>
</fieldset>

	<input type="hidden" name="option" value="com_rsform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="formId" value="<?php echo $this->row->formId; ?>" />
</form>

<script type="text/javascript">
	function returnQuickFields()
	{
		var quickfields = new Array();
		
		<?php foreach ($this->quickfields as $quickfield) { ?>
		quickfields.push('<?php echo $quickfield; ?>');
		<?php } ?>
		
		return quickfields;
	}
	
<?php $update = JRequest::getInt('update',0); ?>
<?php if ($update) echo 'window.parent.updateemails('.JRequest::getInt('formId',0).')'; ?>
</script>

<?php JHTML::_('behavior.keepalive'); ?>