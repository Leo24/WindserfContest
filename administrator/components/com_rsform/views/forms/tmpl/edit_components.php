<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
	<ul class="rsform_leftnav" id="rsform_firstleftnav">
		<?php $this->triggerEvent('rsfp_onBeforeShowComponents');?>
		<li class="rsform_navtitle"><?php echo JText::_('RSFP_FORM_FIELDS'); ?></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('1');return false;" id="rsfpc1"><span id="textbox"><?php echo JText::_('RSFP_COMP_TEXTBOX'); ?></span></a></li>     
		<li><a href="javascript: void(0);" onclick="displayTemplate('2');return false;" id="rsfpc2"><span id="textarea"><?php echo JText::_('RSFP_COMP_TEXTAREA'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('3');return false;" id="rsfpc3"><span id="dropdown"><?php echo JText::_('RSFP_COMP_DROPDOWN'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('4');return false;" id="rsfpc4"><span id="checkboxgroup"><?php echo JText::_('RSFP_COMP_CHECKBOX'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('5');return false;" id="rsfpc5"><span id="radiogroup"><?php echo JText::_('RSFP_COMP_RADIO'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('13');return false;" id="rsfpc13"><span id="submitbutton"><?php echo JText::_('RSFP_COMP_SUBMITBUTTON'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('14');return false;" id="rsfpc14"><span id="password"><?php echo JText::_('RSFP_COMP_PASSWORD'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('9');return false;" id="rsfpc9"><span id="fileupload"><?php echo JText::_('RSFP_COMP_FILE'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('10');return false;" id="rsfpc10"><span id="freetext"><?php echo JText::_('RSFP_COMP_FREETEXT'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('6');return false;" id="rsfpc6"><span id="calendar"><?php echo JText::_('RSFP_COMP_CALENDAR'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('7');return false;" id="rsfpc7"><span id="button"><?php echo JText::_('RSFP_COMP_BUTTON'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('12');return false;" id="rsfpc12"><span id="imagebutton"><?php echo JText::_('RSFP_COMP_IMAGEBUTTON'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('8');return false;" id="rsfpc8"><span id="captchaantispam"><?php echo JText::_('RSFP_COMP_CAPTCHA'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('11');return false;" id="rsfpc11"><span id="hiddenfield"><?php echo JText::_('RSFP_COMP_HIDDEN'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('15');return false;" id="rsfpc15"><span id="supportticket"><?php echo JText::_('RSFP_COMP_TICKET'); ?></span></a></li>
		<li class="rsform_navtitle"><?php echo JText::_('RSFP_MULTIPAGE'); ?></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('41');return false;" id="rsfpc41"><span id="pagebreak"><?php echo JText::_('RSFP_PAGE_BREAK'); ?></span></a></li>
		<?php $this->triggerEvent('rsfp_bk_onAfterShowComponents'); ?>
	</ul>
	
	<input type="hidden" name="componentIdToEdit" id="componentIdToEdit" value="-1" />
	<input type="hidden" name="componentEditForm" id="componentEditForm" value="-1" />

<div id="componentscontent">
	<table border="0" width="100%" class="adminrsform">
		<tr>
			<td valign="top" class="componentPreview">
				<div class="rsform_error" id="rsform_layout_msg" <?php if ($this->form->FormLayoutAutogenerate) { ?>style="display: none"<?php } ?>>
					<?php echo JText::_('RSFP_AUTOGENERATE_LAYOUT_DISABLED'); ?>
				</div>
				<div class="rsform_error" id="rsform_submit_button_msg" <?php if ($this->hasSubmitButton) { ?>style="display: none"<?php } ?>>
					<img src="components/com_rsform/assets/images/submit-help.jpg" alt="" /> <?php echo JText::_('RSFP_NO_SUBMIT_BUTTON'); ?>
				</div>
					<table border="0" id="componentPreview" class="adminlist table table-striped">
						<thead>
						<tr>
							<th class="title" width="1"><input type="hidden" value="-2" name="previewComponentId"/><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/></th>
							<th class="title"><?php echo JText::_('NAME');?></th>
							<th class="title"><?php echo JText::_('RSFP_CAPTION');?></th>
							<th class="title"><?php echo JText::_('PREVIEW');?></th>
							<th class="title" width="5"><?php echo JText::_('EDIT');?></th>
							<th class="title" width="5"><?php echo JText::_('DELETE');?></th>
							<th width="200" class="nowrap center"><?php echo JText::_('Ordering'); ?> <?php echo JHTML::_('grid.order',$this->fields); ?></th>
							<th class="title" width="5"><?php echo JText::_('PUBLISHED');?></th>
							<th class="title" width="5" nowrap="nowrap"><?php echo JText::_('RSFP_COMP_FIELD_REQUIRED');?></th>
							<th class="title" width="5" nowrap="nowrap"><?php echo JText::_('RSFP_COMP_FIELD_VALIDATIONRULE');?></th>
						</tr>
						</thead>
						<tbody>
						<?php
						$i = 0;
						$k = 0;
						$n = count($this->fields);
						// hack to show order down icon
						$n++;
						foreach ($this->fields as $field) { ?>
						<tr class="row<?php echo $k; ?><?php if ($field->type_id == 41) { ?> rsform_page<?php } ?>">
							<td><input type="hidden" name="previewComponentId" value="<?php echo $field->id; ?>" /><?php echo JHTML::_('grid.id', $i, $field->id); ?></td>
							<td><?php echo $field->name; ?></td>
							<?php echo $field->preview; ?>
							<td align="center"><span class="hasTip" title="<?php echo JText::_('RSFP_EDIT_COMPONENT'); ?>"><a href="javascript:void(0);" onclick="displayTemplate('<?php echo $field->type_id; ?>','<?php echo $field->id; ?>');"><img src="components/com_rsform/assets/images/icons/edit.png" border="0" width="16" height="16" alt="<?php echo JText::_('RSFP_EDIT_COMPONENT'); ?>" /></a></span></td>
							<td align="center"><span class="hasTip" title="<?php echo JText::_('RSFP_REMOVE_COMPONENT'); ?>"><a href="javascript:void(0);" onclick="if (confirm('<?php echo JText::sprintf('RSFP_REMOVE_COMPONENT_CONFIRM', $field->name); ?>')) removeComponent('<?php echo $this->form->FormId; ?>','<?php echo $field->id; ?>');"><img src="components/com_rsform/assets/images/icons/remove.png" border="0" width="16" height="16" alt="<?php echo JText::_('RSFP_REMOVE_COMPONENT'); ?>" /></a></span></td>
							<td class="order center">
								<span><?php echo $this->pagination->orderUpIcon( $i, true, 'orderup', 'Move Up', 'ordering'); ?></span>
								<span><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', 'ordering' ); ?></span>
								<input type="text" name="order[]" size="5" value="<?php echo $field->ordering; ?>" disabled="disabled" class="width-20 text-area-order" style="text-align:center" />
							</td>
							<td align="center" id="publishcb<?php echo $i; ?>"><?php echo JHTML::_('jgrid.published', $field->published, $i, 'components.'); ?></td>
							<td align="center" id="requiredcb<?php echo $i; ?>"><?php echo is_bool($field->required) ?
							JHtml::_('jgrid.state', array(
								0 => array('setrequired', 'JYES', '', '', false, 'unpublish', 'unpublish'),
								1 => array('unsetrequired', 'JNO', '', '', false, 'publish', 'publish')
							), $field->required, $i, 'components.')
							: '-'; ?></td>
							<td align="center"><?php echo $field->validation; ?></td>
						</tr>
						<?php
						$i++;
						$k=1-$k;
						}
						?>
						</tbody>
					</table>
			</td>
		</tr>
	</table>
</div>