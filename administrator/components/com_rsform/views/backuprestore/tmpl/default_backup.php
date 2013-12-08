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
	<legend><?php echo JText::_('RSFP_BACKUP_RESTORE_INSTRUCTIONS'); ?></legend>
	<?php echo JText::sprintf('RSFP_BACKUP_RESTORE_INSTRUCTIONS_DESC', JText::_('RSFP_BACKUP_GENERATE'), JText::_('RSFP_RESTORE')); ?>
</fieldset>

<table class="adminheading" width="100%">
	<?php if ($this->writable) { ?>
	<tr>
		<td>
			<table class="adminlist table table-striped" id="articleList">
			<thead>
	        <tr>
	            <th width="1"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /></th>
	            <th class="title"><?php echo JText::_('RSFP_FORM_TITLE'); ?></th>
	            <th class="title"><?php echo JText::_('RSFP_FORM_NAME'); ?></th>
	            <th class="title" width="15"><?php echo JText::_('RSFP_SUBMISSIONS'); ?></th>
	        </tr>
			</thead>
	        <?php
	        $i = 0;
			$k = 0;
	        foreach ($this->forms as $row) { ?>
	        <tr class="row<?php echo $k; ?>">
				<td><?php echo JHTML::_('grid.id', $i, $row->FormId); ?></td>
	            <td><label for="cb<?php echo $i; ?>"><?php echo !empty($row->FormTitle) ? strip_tags($row->FormTitle) : '<em>no title</em>'; ?></label></td>
	            <td><?php echo $row->FormName; ?></td>
	            <td><?php echo $row->_allSubmissions; ?></td>
	        </tr>
			<?php
	            $i++;
				$k=1-$k;
	        }
			?>
			<tr>
				<td width="1%"><input type="checkbox" name="submissions" id="submissions" value="1" /></td>
				<td colspan="3"><label for="submissions"><strong class="rsform_notok"><?php echo JText::_('RSFP_BACKUP_SUBMISSIONS');?></strong></label></td>
			</tr>
	        </table>
			<button class="rs_button" type="button" onclick="submitbutton('backup.download')"><?php echo JText::_('RSFP_BACKUP_GENERATE'); ?></button>
		</td>
	</tr>
	<?php } else { ?>
	<tr>
		<th class="dbbackup">
			<?php echo JText::_('RSFP_BACKUP_NOT_WRITABLE');?>
		</th>
	</tr>
	<?php }	?>
</table>