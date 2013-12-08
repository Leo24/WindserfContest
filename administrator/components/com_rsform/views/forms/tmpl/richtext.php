<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<form method="post" action="index.php?option=com_rsform" name="adminForm">
	<p>
		<button class="rs_button rs_left" type="button" onclick="submitform('richtext.apply');"><?php echo JText::_('APPLY'); ?></button>
		<button class="rs_button rs_left" type="button" onclick="submitform('richtext.save');"><?php echo JText::_('SAVE'); ?></button>
		<button class="rs_button rs_left" type="button" onclick="window.close();"><?php echo JText::_('CLOSE'); ?></button>
	</p>
	<span class="rsform_clear_both"></span>
	
	<fieldset>
	<legend><?php echo JText::_('RSFP_EDITING_TEXT'); ?></legend>
	
	<?php if ($this->noEditor) { ?>
		<textarea cols="70" rows="10" style="width: 500px; height: 320px;" class="rs_textarea" name="<?php echo $this->editorName; ?>"><?php echo RSFormProHelper::htmlEscape($this->editorText); ?></textarea>
	<?php } else { ?>
		<?php echo $this->editor->display($this->editorName, htmlentities($this->editorText, ENT_COMPAT, 'UTF-8'), 500, 320, 70, 10); ?>
	<?php } ?>
	</fieldset>
	
	<input type="hidden" name="option" value="com_rsform" />
	<input type="hidden" name="opener" value="<?php echo $this->editorName; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="formId" value="<?php echo $this->formId; ?>" />
</form>

<?php JHTML::_('behavior.keepalive'); ?>