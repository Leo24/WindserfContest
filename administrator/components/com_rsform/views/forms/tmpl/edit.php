<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
JHTML::_('behavior.calendar');
?>

<form action="index.php?option=com_rsform&amp;task=forms.edit&amp;formId=<?php echo $this->form->FormId; ?>" method="post" name="adminForm" id="adminForm">

<span><?php echo $this->lists['Languages']; ?></span>
<span><?php echo JText::sprintf('RSFP_YOU_ARE_EDITING_IN', $this->lang, RSFormProHelper::translateIcon()); ?></span>

<div id="rsform_container">
	<div id="state" style="display: none;"><img src="components/com_rsform/assets/images/load.gif" alt="<?php echo JText::_('RSFP_PROCESSING'); ?>" /><?php echo JText::_('RSFP_PROCESSING'); ?></div>
	
	<ul id="rsform_maintabs">
		<li><a href="javascript: void(0);" id="components"><span><?php echo JText::_('RSFP_COMPONENTS_TAB_TITLE'); ?></span></a></li>
	    <li><a href="javascript: void(0);" id="properties"><span><?php echo JText::_('RSFP_PROPERTIES_TAB_TITLE'); ?></span></a></li>
	</ul>
	<div id="rsform_tab1">		
		<div id="rsform_fixed">
			<div id="rsform_textboxdiv" class="rsform_hide">
				<ul class="rsform_secondarytabs">
					<li><a href="javascript: void(0);" id="rsfptab0" class="active"><?php echo JText::_('RSFP_COMPONENTS_GENERAL_TAB'); ?></a></li>
					<li><a href="javascript: void(0);" id="rsfptab1"><?php echo JText::_('RSFP_COMPONENTS_VALIDATIONS_TAB'); ?></a></li>
					<li><a href="javascript: void(0);" id="rsfptab2"><?php echo JText::_('RSFP_COMPONENTS_ATRIBUTES_TAB'); ?></a></li>
				</ul>
				
				<div id="rsform_secondarytabcontent">
					<div id="rsfptabcontent0"></div>
					<div id="rsfptabcontent1"></div>
					<div id="rsfptabcontent2"></div>
				</div>
				<a href="javascript: void(0);" class="rsform_close"></a>
			</div>
		</div>
		
		<?php echo $this->loadTemplate('components'); ?>
	</div>
	
	<div id="rsform_tab2">
		<ul class="rsform_leftnav" id="rsform_secondleftnav">
			<li class="rsform_navtitle"><?php echo JText::_('RSFP_DESIGN_TAB'); ?></li>
			<li><a href="javascript: void(0);" id="formlayout"><span><?php echo JText::_('RSFP_FORM_LAYOUT'); ?></span></a></li>
			<li><a href="javascript: void(0);" id="formtheme"><span><?php echo JText::_('RSFP_FORM_THEME'); ?></span></a></li>
			<li><a href="javascript: void(0);" id="cssandjavascript"><span><?php echo JText::_('RSFP_CSS_JS'); ?></span></a></li>
			<?php $this->triggerEvent('rsfp_bk_onAfterShowFormDesignTabsTab'); ?>
			<li class="rsform_navtitle"><?php echo JText::_('RSFP_FORM_TAB'); ?></li>
			<li><a href="javascript: void(0);" id="editform"><span><?php echo JText::_('RSFP_FORM_EDIT'); ?></span></a></li>
			<li><a href="javascript: void(0);" id="editformattributes"><span><?php echo JText::_('RSFP_FORM_EDIT_ATTRIBUTES'); ?></span></a></li>
			<li><a href="javascript: void(0);" id="metatags"><span><?php echo JText::_('RSFP_FORM_META_TAGS'); ?></span></a></li>
			<?php $this->triggerEvent('rsfp_bk_onAfterShowFormFormTabsTab'); ?>
			<li class="rsform_navtitle"><?php echo JText::_('RSFP_EMAILS_TAB'); ?></li>
			<li><a href="javascript: void(0);" id="useremails"><span><?php echo JText::_('RSFP_USER_EMAILS'); ?></span></a></li>
			<li><a href="javascript: void(0);" id="adminemails"><span><?php echo JText::_('RSFP_ADMIN_EMAILS'); ?></span></a></li>
			<li><a href="javascript: void(0);" id="emails"><span><?php echo JText::_('RSFP_FORM_EMAILS'); ?></span></a></li>
			<?php $this->triggerEvent('rsfp_bk_onAfterShowFormEmailsTabsTab'); ?>
			<li class="rsform_navtitle"><?php echo JText::_('RSFP_SCRIPTS_TAB'); ?></li>
			<li><a href="javascript: void(0);" id="scripts"><span><?php echo JText::_('RSFP_FORM_SCRIPTS'); ?></span></a></li>
			<li><a href="javascript: void(0);" id="emailscripts"><span><?php echo JText::_('RSFP_EMAIL_SCRIPTS'); ?></span></a></li>
			<?php $this->triggerEvent('rsfp_bk_onAfterShowFormScriptsTabsTab'); ?>
			<li class="rsform_navtitle"><?php echo JText::_('RSFP_EXTRAS_TAB'); ?></li>
			<li><a href="javascript: void(0);" id="mappings"><span><?php echo JText::_('RSFP_FORM_MAPPINGS'); ?></span></a></li>
			<li><a href="javascript: void(0);" id="conditions"><span><?php echo JText::_('RSFP_CONDITIONAL_FIELDS'); ?></span></a></li>
			<li><a href="javascript: void(0);" id="postscript"><span><?php echo JText::_('RSFP_POST_TO_LOCATION'); ?></span></a></li>
			<?php $this->triggerEvent('rsfp_bk_onAfterShowFormEditTabsTab'); ?>
		</ul>
		
		<div id="propertiescontent">
			<div id="formlayoutdiv">
				<p><?php echo $this->loadTemplate('layout'); ?></p>
			</div><!-- formlayout -->
			<div id="formthemediv">
				<p><?php echo $this->loadTemplate('theme'); ?></p>
			</div><!-- formthemediv -->
			<div id="cssandjavascriptdiv">
				<p><?php echo $this->loadTemplate('cssjs'); ?></p>
			</div><!-- cssandjavascript -->
			<?php $this->triggerEvent('rsfp_bk_onAfterShowFormDesignTabs'); ?>
			<div id="editformdiv">
				<p><?php echo $this->loadTemplate('form'); ?></p>
			</div><!-- editform -->
			<div id="editformattributesdiv">
				<p><?php echo $this->loadTemplate('formattr'); ?></p>
			</div><!-- editformattributes -->
			<div id="metatagsdiv">
				<p><?php echo $this->loadTemplate('meta'); ?></p>
			</div><!-- metatags -->
			<?php $this->triggerEvent('rsfp_bk_onAfterShowFormFormTabs'); ?>
			<div id="useremailsdiv">
				<p><?php echo $this->loadTemplate('user'); ?></p>
			</div><!-- useremails -->
			<div id="adminemailsdiv">
				<p><?php echo $this->loadTemplate('admin'); ?></p>
			</div><!-- adminemails -->
			<div id="emailsdiv">
				<p><?php echo $this->loadTemplate('emails'); ?></p>
			</div><!-- emails -->
			<?php $this->triggerEvent('rsfp_bk_onAfterShowFormEmailsTabs'); ?>
			<div id="scriptsdiv">
				<p><?php echo $this->loadTemplate('scripts'); ?></p>
			</div><!-- scripts -->
			<div id="emailscriptsdiv">
				<p><?php echo $this->loadTemplate('emailscripts'); ?></p>
			</div><!-- emailscripts -->
			<?php $this->triggerEvent('rsfp_bk_onAfterShowFormScriptsTabs'); ?>
			<div id="mappingsdiv">
				<p><?php echo $this->loadTemplate('mappings'); ?></p>
			</div><!-- mappings -->
			<div id="conditionsdiv">
				<p><?php echo $this->loadTemplate('conditions'); ?></p>
			</div>
			<div id="postscriptdiv">
				<p><?php echo $this->loadTemplate('post'); ?></p>
			</div><!-- postscriptdiv -->
			<?php $this->triggerEvent('rsfp_bk_onAfterShowFormEditTabs'); ?>
		</div>
	</div>
</div>
	
	<input type="hidden" name="tabposition" id="tabposition" value="0" />
	<input type="hidden" name="tab" id="ptab" value="0" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="formId" id="formId" value="<?php echo $this->form->FormId; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_rsform" />
	<input type="hidden" name="Lang" value="<?php echo $this->form->Lang; ?>" />
	<?php if (JRequest::getVar('tmpl') == 'component') { ?>
	<input type="hidden" name="tmpl" value="component" />
	<?php } ?>
</form>
	
<script type="text/javascript">
RSFormPro.$(document).ready(function(){
	RSFormPro.$('#rsform_tab2').formTabs(<?php echo $this->tab; ?>);
	RSFormPro.$('#rsform_textboxdiv').formTabs(0);
	<?php if (!$this->tabposition) { ?>
	RSFormPro.$("#rsform_tab1").show();
	RSFormPro.$("#rsform_tab2").hide();
	RSFormPro.$("#properties").removeClass('active');
	RSFormPro.$("#components").addClass('active');
	<?php } else { ?>
	RSFormPro.$("#rsform_tab2").show();
	RSFormPro.$("#rsform_tab1").hide();
	RSFormPro.$("#properties").addClass('active');
	RSFormPro.$("#components").removeClass('active');
	<?php } ?>
});

RSFormPro.$.formTabs = {
	tabTitles: {},
	tabContents: {},
	
	build: function (startindex) {
		this.each(function (index, el) {
			var tid = RSFormPro.$(el).attr('id');
			RSFormPro.$.formTabs.grabElements(el,tid);
			RSFormPro.$.formTabs.makeTitlesClickable(tid);
			RSFormPro.$.formTabs.setAllContentsInactive(tid);
			RSFormPro.$.formTabs.setTitleActive(startindex,tid);
			RSFormPro.$.formTabs.setContentActive(startindex,tid);
		});
	},
	
	grabElements: function(el,tid) {
		var children = RSFormPro.$(el).children();
		children.each(function(index, child) {			
			if (index == 0)
				RSFormPro.$.formTabs.tabTitles[tid] = RSFormPro.$(child).find('a');
			else if (index == 1)
				RSFormPro.$.formTabs.tabContents[tid] = RSFormPro.$(child).children();
		});
	},
	
	setAllTitlesInactive: function (tid) {
		this.tabTitles[tid].each(function(index, title) {
			RSFormPro.$(title).removeClass('active');
		});
	},
	
	setTitleActive: function (index,tid) {
		index = parseInt(index);
		if (tid == 'rsform_tab2') document.getElementById('ptab').value = index;
		RSFormPro.$(this.tabTitles[tid][index]).addClass('active');
	},
	
	setAllContentsInactive: function (tid) {
		this.tabContents[tid].each(function(index, content) {
			RSFormPro.$(content).hide();
		});
	},
	
	setContentActive: function (index,tid) {
		index = parseInt(index);
		RSFormPro.$(this.tabContents[tid][index]).show();
	},
	
	makeTitlesClickable: function (tid) {
		this.tabTitles[tid].each(function(index, title) {
			RSFormPro.$(title).click(function () {
				RSFormPro.$.formTabs.setAllTitlesInactive(tid);
				RSFormPro.$.formTabs.setTitleActive(index,tid);
				
				RSFormPro.$.formTabs.setAllContentsInactive(tid);
				RSFormPro.$.formTabs.setContentActive(index,tid);
			});
		});
	}
}

RSFormPro.$.fn.extend({
	formTabs: RSFormPro.$.formTabs.build
});

function submitbutton(pressbutton)
{
	var form = document.adminForm;
	
	if (pressbutton == 'forms.cancel')
	{
		removeExtraItems();
		submitform(pressbutton);
		return;
	}
	else if (pressbutton == 'forms.preview')
	{
		window.open('<?php echo JURI::root(); ?>index.php?option=com_rsform&formId=<?php echo $this->form->FormId; ?>');
		return;
	}
	else if (pressbutton == 'components.copy' || pressbutton == 'components.duplicate')
	{		
		if (form.boxchecked.value == 0)
		{
			alert('<?php echo addslashes(JText::sprintf('RSFP_PLEASE_MAKE_SELECTION_TO', JText::_('RSFP_COPY'))); ?>');
			return;
		}
		removeExtraItems();
		submitform(pressbutton);
	}
	else if (pressbutton == 'components.remove' || pressbutton == 'components.publish' || pressbutton == 'components.unpublish' || pressbutton == 'components.save')
	{
		removeExtraItems();
		submitform(pressbutton);
	}
	else
	{
		// do field validation
		if (document.getElementById('FormName').value == '')
			alert('<?php echo JText::_('RSFP_UNIQUE_NAME_MSG', true);?>');
		else
		{
			if (RSFormPro.$('#properties').hasClass('active'))
				document.getElementById('tabposition').value = 1;	
			submitform(pressbutton);
		}
	}
}

<?php if (RSFormProHelper::isJ16()) { ?>
	Joomla.submitbutton = submitbutton;
<?php } ?>

function removeExtraItems()
{
	var form = document.adminForm;
	
	form.formLayout.name = '';
	form.CSS.name = '';
	form.JS.name = '';
	form.ScriptDisplay.name = '';
	form.ScriptProcess.name = '';
	form.ScriptProcess2.name = '';
	form.UserEmailScript.name = '';
	form.AdminEmailScript.name = '';
	form.MetaDesc.name = '';
	form.MetaKeywords.name = '';
}

function listItemTask(cb, task)
{
	if (task == 'orderdown' || task == 'orderup')
	{
		var table = RSFormPro.$('#componentPreview');
		currentRow = RSFormPro.$(document.getElementById(cb)).parent().parent();		
		if (task == 'orderdown')
		{
			try { currentRow.insertAfter(currentRow.next()); }
			catch (dnd_e) { }
		}
		if (task == 'orderup')
		{
			try { currentRow.insertBefore(currentRow.prev()); }
			catch (dnd_e) { }
		}
		
		tidyOrder(true);
		return;
	}
	
	stateLoading();
	
	xml=buildXmlHttp();
	var url = 'index.php?option=com_rsform&task=' + task + '&format=raw&randomTime=' + Math.random();
	
	xml.open("POST", url, true);
	
	params = new Array();
	params.push('i=' + cb);
	params.push('componentId=' + document.getElementById(cb).value);
	params.push('formId=<?php echo $this->form->FormId; ?>');
	params = params.join('&');
	
	//Send the proper header information along with the request
	xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xml.setRequestHeader("Content-length", params.length);
	xml.setRequestHeader("Connection", "close");

	switch (task) {
		case 'components.unpublish':
		case 'components.publish':
			var theId = 'publish' + cb;
		break;
		
		case 'components.unsetrequired':
		case 'components.setrequired':
			var theId = 'required' + cb;
		break;
	}
	
	xml.send(params);
	xml.onreadystatechange=function()
	{
		if(xml.readyState==4)
		{
			var cell = document.getElementById(theId);
			RSFormPro.$(cell).html(xml.responseText);
			
			stateDone();
			
			if (document.getElementById('FormLayoutAutogenerate1').checked==true)
				generateLayout(<?php echo $this->form->FormId; ?>, 'no');
		}
	}
}

function orderMapping(mp, task)
{
	if (task == 'orderdown' || task == 'orderup')
	{
		var table = RSFormPro.$('#mappingTable');
		currentRow = RSFormPro.$(document.getElementById(mp)).parent().parent();		
		if (task == 'orderdown')
		{
			try { currentRow.insertAfter(currentRow.next()); }
			catch (dnd_e) { }
		}
		if (task == 'orderup')
		{
			try { currentRow.insertBefore(currentRow.prev()); }
			catch (dnd_e) { }
		}
		
		tidyOrderMp(true);
		return;
	}
}

function saveorder(num, task)
{
	tidyOrder(true);
}

function returnQuickFields()
{
	var quickfields = new Array();
	
	<?php foreach ($this->quickfields as $quickfield) { ?>
	quickfields.push('<?php echo $quickfield; ?>');
	<?php } ?>
	
	return quickfields;
}

function enableAttachFile(value)
{
	if (value == 1)
	{
		document.getElementById('rsform_select_file').style.display = '';
		document.getElementById('UserEmailAttachFile').disabled = false;
	}
	else
	{
		document.getElementById('rsform_select_file').style.display = 'none';
		document.getElementById('UserEmailAttachFile').disabled = true;
	}
}

function enableEmailMode(type, value)
{
	var opener = type == 'User' ? 'UserEmailText' : 'AdminEmailText';
	var id = type == 'User' ? 'rsform_edit_user_email' : 'rsform_edit_admin_email';
	// HTML
	if (value == 1)
	{
		document.getElementById(id).setAttribute('onclick', "openRSModal('index.php?option=com_rsform&task=richtext.show&opener=" + opener + "&formId=<?php echo $this->form->FormId; ?>&tmpl=component')");
	}
	// Text
	else
	{
		document.getElementById(id).setAttribute('onclick', "openRSModal('index.php?option=com_rsform&task=richtext.show&opener=" + opener + "&formId=<?php echo $this->form->FormId; ?>&tmpl=component&noEditor=1')");
	}
}

function enableThankyou(value)
{
	if (value == 1)
	{
		document.getElementById('ShowContinue0').disabled = false;
		document.getElementById('ShowContinue1').disabled = false;
	}
	else
	{
		document.getElementById('ShowContinue0').disabled = true;
		document.getElementById('ShowContinue1').disabled = true;
	}
}

function RStranslateText(thetext)
{
	if (thetext == 'extra')
		return '<?php echo JText::_('RSFP_COMP_FIELD_VALIDATIONEXTRA', true); ?>';
	else if (thetext == 'regex')
		return '<?php echo JText::_('RSFP_COMP_FIELD_VALIDATIONEXTRAREGEX', true); ?>';
}

toggleQuickAdd();
</script>
	
<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>