<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<?php if (!$this->isComponent) { ?>
<div class="rsform_error"><?php echo JText::_('RSFP_CONDITION_MULTILANGUAGE_WARNING'); ?></div>
<br />
<div id="conditionscontent" style="overflow: auto;">
<?php } ?>
<button type="button" class="rs_button" onclick="openRSModal('<?php echo JRoute::_('index.php?option=com_rsform&view=conditions&layout=edit&formId='.$this->formId.'&tmpl=component'); ?>', 'Conditions', '800x600')"><?php echo JText::_('RSFP_FORM_CONDITION_NEW'); ?></button>
<br /><br />

	<table class="adminlist table table-striped" id="conditionsTable">
	<thead>
		<tr>
			<th nowrap="nowrap"><?php echo JText::_('RSFP_CONDITION_FIELD_NAME'); ?></th>
			<th width="1%" class="title" nowrap="nowrap"><?php echo JText::_('RSFP_CONDITIONS_ACTIONS'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php $i = 0; $k = 0; $n = count($this->conditions); ?>
		<?php if (!empty($this->conditions)) { ?>
		<?php foreach ($this->conditions as $row) { ?>
		<tr class="row<?php echo $k; ?>">
			<td>
				<a href="#" onclick="openRSModal('<?php echo JRoute::_('index.php?option=com_rsform&view=conditions&layout=edit&tmpl=component&formId='.$this->formId.'&cid='.$row->id); ?>', 'Conditions', '800x600'); return false;">(<?php echo JText::_('RSFP_CONDITION_'.$row->action); ?>) <?php echo $this->escape($row->ComponentName); ?></a>
			</td>
			<td align="center" width="20%" nowrap="nowrap">
				<button type="button" class="rs_button rs_left" onclick="openRSModal('<?php echo JRoute::_('index.php?option=com_rsform&view=conditions&layout=edit&tmpl=component&formId='.$this->formId.'&cid='.$row->id); ?>', 'Conditions', '800x600')"><?php echo JText::_('EDIT'); ?></button>
				<button type="button" class="rs_button rs_left" onclick="if (confirm('<?php echo JText::_('RSFP_CONDITION_DELETE_SURE', true); ?>')) conditionDelete(<?php echo $this->formId; ?>,<?php echo $row->id; ?>);"><?php echo JText::_('DELETE'); ?></button>
			</td>
		</tr>
		<?php $k=1-$k; ?>
		<?php $i++; ?>
		<?php } ?>
		<?php } ?>
	</tbody>
	</table>
<?php if (!$this->isComponent) { ?>
</div>
<?php } ?>