<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<table class="adminheading" width="100%">
	<?php if ($this->writable) { ?>
	<tr>
		<td align="left" width="1%" nowrap="nowrap"><label for="userfile"><?php echo JText::_('RSFP_PACKAGE_FILE');?></label></td>
		<td><input class="text_area" name="userfile" id="userfile" type="file" size="70"/></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td class="com-rsform-css-fix"><input type="checkbox" id="overwrite_checkbox" name="overwrite" value="1"/> <label for="overwrite_checkbox"><?php echo JText::_('RSFP_OVERWRITE_FORMS');?></label></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><button class="rs_button" type="button" onclick="submitbutton('restore.process')"><?php echo JText::_('RSFP_RESTORE');?></button></td>
	</tr>
	<?php } else { ?>
	<tr>
		<th class="dbbackup">
			<?php echo JText::_('RSFP_RESTORE_NOT_WRITABLE');?>
		</th>
	</tr>
	<?php } ?>
</table>