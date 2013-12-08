var RSFormPro = {};
RSFormPro.$ = jQuery;

function initRSFormPro()
{	
	RSFormPro.$('#componentPreview tbody').tableDnD({
			onDragClass: 'rsform_dragged',
			onDrop: function (table, row) {
				tidyOrder(true);
			}
		});
	
	RSFormPro.$('#mappingTable tbody').tableDnD({
			onDragClass: 'rsform_dragged',
			onDrop: function (table, row) {
				tidyOrderMp(true);
			}
		});
		
	toggleOrderSpans();
	toggleOrderSpansMp();
		
	$$('a.rsmodal').each(function(el) {
			el.addEvent('click', function(e) {
				new Event(e).stop();
				openRSModal(el.href);
			});
		});
		
	RSFormPro.$(document).click(function() { closeAllDropdowns(); });
	
	RSFormPro.$("#rsform_tab2").hide();  
  
	RSFormPro.$("#properties").click(function()
	{
		RSFormPro.$("#rsform_tab2").show();   
		RSFormPro.$("#rsform_tab1").hide();
		RSFormPro.$("#components").removeClass('active');
		RSFormPro.$("#properties").addClass('active');
	});
	
	RSFormPro.$("#components").click(function()
	{
		RSFormPro.$("#rsform_tab1").show();   
		RSFormPro.$("#rsform_tab2").hide();
		RSFormPro.$("#properties").removeClass('active');
		RSFormPro.$("#components").addClass('active');
	});
  
	RSFormPro.$(".rsform_hide").hide();
	
	RSFormPro.$("div a.rsform_close").click(function()
	{
		RSFormPro.$(this).parent().animate({width: 'toggle'});
		
		RSFormPro.$('#rsform_firstleftnav li a').each(function(index,el) {
			RSFormPro.$(el).removeClass('active');
		});
	});  
	
}

function buildXmlHttp()
{
	var xmlHttp;
	try
	{
		xmlHttp=new XMLHttpRequest();
	}
	catch (e)
	{
		try
		{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			try
			{
				xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				alert("Your browser does not support AJAX!");
				return false;
			}
		}
	}
	return xmlHttp;
}

function tidyOrder(update_php)
{
	if (!update_php)
		update_php = false;
		
	stateLoading();
	
	var params = new Array();
	
	var must_update_php = update_php;
	var orders = document.getElementsByName('order[]');
	var cids = document.getElementsByName('cid[]');
	for (i=0; i<orders.length; i++)
	{
		params.push('cid_' + cids[i].value + '=' + parseInt(i + 1));
		
		if (orders[i].value != i + 1)
			must_update_php = true;
		
		orders[i].value = i + 1;
	}
	
	toggleOrderSpans();
	
	if (update_php && must_update_php)
	{
		xml=buildXmlHttp();

		var url = 'index.php?option=com_rsform&task=components.save.ordering&randomTime=' + Math.random();
		xml.open("POST", url, true);
		
		params = params.join('&');
		
		//Send the proper header information along with the request
		xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xml.setRequestHeader("Content-length", params.length);
		xml.setRequestHeader("Connection", "close");

		xml.send(params);
		xml.onreadystatechange=function()
		{
			if(xml.readyState==4)
			{
				formId = document.getElementById('formId').value;
				if (document.getElementById('FormLayoutAutogenerate1').checked == true)
					generateLayout(formId, 'no');
					
				stateDone();
			}
		}
	}
	else
	{
		stateDone();
	}
}


function tidyOrderMp(update_php)
{
	if (!update_php)
		update_php = false;
		
	stateLoading();
	
	var params = new Array();
	
	var must_update_php = update_php;
	var orders = document.getElementsByName('mporder[]');
	var cids = document.getElementsByName('mpid[]');
	for (i=0; i<orders.length; i++)
	{
		params.push('mpid_' + cids[i].value + '=' + parseInt(i + 1));
		
		if (orders[i].value != i + 1)
			must_update_php = true;
		
		orders[i].value = i + 1;
	}
	
	toggleOrderSpansMp();
	
	if (update_php && must_update_php)
	{
		xml=buildXmlHttp();

		var url = 'index.php?option=com_rsform&task=ordering&controller=mappings&randomTime=' + Math.random();
		xml.open("POST", url, true);
		
		params = params.join('&');
		
		//Send the proper header information along with the request
		xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xml.setRequestHeader("Content-length", params.length);
		xml.setRequestHeader("Connection", "close");

		xml.send(params);
		xml.onreadystatechange=function()
		{
			if(xml.readyState==4)
			{					
				stateDone();
			}
		}
	}
	else
	{
		stateDone();
	}
}

function toggleOrderSpans()
{
	var table = RSFormPro.$('#componentPreview tbody tr');
	var k = 0;
	for (i=0; i<table.length; i++)
	{
		RSFormPro.$(table[i]).removeClass('row0');
		RSFormPro.$(table[i]).removeClass('row1');
		RSFormPro.$(table[i]).addClass('row' + k);
		k = 1 - k;
	}
	
	RSFormPro.$('table#componentPreview td.order').each(function(index, el) {
		var spans = RSFormPro.$(el).children();
		spans.removeAttr('style');
		if (index == 0)
			RSFormPro.$(spans[0]).css('visibility', 'hidden');
		else if (index == table.length - 1)
			RSFormPro.$(spans[1]).css('visibility', 'hidden');
	});
}

function toggleOrderSpansMp()
{
	var table = RSFormPro.$('#mappingTable tbody tr');
	var k = 0;
	for (i=0; i<table.length; i++)
	{
		RSFormPro.$(table[i]).removeClass('row0');
		RSFormPro.$(table[i]).removeClass('row1');
		RSFormPro.$(table[i]).addClass('row' + k);
		k = 1 - k;
	}
	
	RSFormPro.$('table#mappingTable td.order').each(function(index, el) {
		var spans = RSFormPro.$(el).children();
		spans.removeAttr('style');
		if (index == 0)
			RSFormPro.$(spans[0]).css('visibility', 'hidden');
		else if (index == table.length - 1)
			RSFormPro.$(spans[1]).css('visibility', 'hidden');
	});
}

function displayTemplate(componentTypeId,componentId)
{
	if ( RSFormPro.$('#rsfpc'+componentTypeId).hasClass('active') && (document.getElementById('componentIdToEdit').value == componentId || !componentId))
	{
		document.getElementById('rsfptabcontent0').innerHTML = '';
		document.getElementById('rsfptabcontent1').innerHTML = '';
		document.getElementById('rsfptabcontent2').innerHTML = '';
		
		RSFormPro.$(".rsform_hide").animate({width: 'toggle'});
		RSFormPro.$('#rsfpc'+componentTypeId).removeClass('active');
		
		return;
	}
	
	document.getElementById('rsfptab0').style.display = '';
	document.getElementById('rsfptab1').style.display = '';
	document.getElementById('rsfptab2').style.display = '';
	
	//hide the editor tab
	RSFormPro.$(".rsform_hide").hide();
	
	RSFormPro.$('#rsfpc'+componentTypeId).addClass('rsform_loading_btn');
	
	//remove the active class
	RSFormPro.$('#rsform_firstleftnav li a').each(function(index,el) {
		RSFormPro.$(el).removeClass('active');
	});
	
	stateLoading();

	document.getElementById('componentIdToEdit').value=-1;
	
	xml=buildXmlHttp();
	xml.onreadystatechange=function()
    {
		if(xml.readyState==4)
		{
			try {
				var top = f_scrollTop();
				if (top > 200)
					RSFormPro.$.scrollTo(RSFormPro.$('#rsform_firstleftnav'), 100);
			}
			catch (err) {
				// do nothing
			}
			
			RSFormPro.$('#rsfpc'+componentTypeId).removeClass('rsform_loading_btn');
			response = xml.responseText.split('{rsfsep}');
			
			if (RSFormPro.$.trim(response[1]) == '')
				document.getElementById('rsfptab1').style.display = 'none';
			if (RSFormPro.$.trim(response[2]) == '')
				document.getElementById('rsfptab2').style.display = 'none';
			
			document.getElementById('rsfptabcontent0').innerHTML = response[0];
			document.getElementById('rsfptabcontent1').innerHTML = response[1];
			document.getElementById('rsfptabcontent2').innerHTML = response[2];
			
			stateDone();
			
			//set the active tab
			RSFormPro.$('#rsfpc'+componentTypeId).addClass('active');
			
			//show the editor tab
			RSFormPro.$(".rsform_hide").animate({width: 'toggle'});
			
			RSFormPro.$('.rsform_secondarytabs li a').each(function(index,el) {
				RSFormPro.$(el).removeClass('active');
			});
			
			RSFormPro.$('#rsform_textboxdiv').formTabs(0);
			
			changeValidation($('VALIDATIONRULE'));
			
			// calendar validation
			if (componentTypeId == 6)
			{
				RSFormPro.$('#MINDATE').bind('keyup', function() { this.value = rsfp_validateDate(this.value); });
				RSFormPro.$('#MAXDATE').bind('keyup', function() { this.value = rsfp_validateDate(this.value); });
				
				Calendar.setup({
					inputField     :    "MINDATE",     // id of the input field
					ifFormat       :    "%m/%d/%Y",      // format of the input field
					button         :    "MINDATE",  // trigger for the calendar (button ID)
					align          :    "Tl",           // alignment (defaults to "Bl")
					singleClick    :    true
				});
				Calendar.setup({
					inputField     :    "MAXDATE",     // id of the input field
					ifFormat       :    "%m/%d/%Y",      // format of the input field
					button         :    "MAXDATE",  // trigger for the calendar (button ID)
					align          :    "Tl",           // alignment (defaults to "Bl")
					singleClick    :    true
				});
			}
		}
    }
	
	if (componentId)
	{
		document.getElementById('componentIdToEdit').value=componentId;
		xml.open('GET','index.php?option=com_rsform&task=components.display&componentType=' + componentTypeId + '&componentId=' + componentId + '&formId=' + document.getElementById('formId').value + '&format=raw&randomTime=' + Math.random(), true);
	}
	else
		xml.open('GET','index.php?option=com_rsform&task=components.display&componentType='+componentTypeId+'&formId='+document.getElementById('formId').value+'&format=raw&randomTime='+Math.random(), true);
		
	xml.send(null);
}

function rsfp_validateDate(value)
{
	value = value.replace(/[^0-9\/]/g, '');	
	return value;
}

function f_scrollTop() {
	return f_filterResults (
		window.pageYOffset ? window.pageYOffset : 0,
		document.documentElement ? document.documentElement.scrollTop : 0,
		document.body ? document.body.scrollTop : 0
	);
}
function f_filterResults(n_win, n_docel, n_body) {
	var n_result = n_win ? n_win : 0;
	if (n_docel && (!n_result || (n_result > n_docel)))
		n_result = n_docel;
	return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}

function removeComponent(formId,componentId)
{
	stateLoading();
	xml=buildXmlHttp();
	xml.onreadystatechange=function()
    {
		if(xml.readyState==4)
		{
			var table=document.getElementById('componentPreview');
			var rows=document.getElementsByName('previewComponentId');
			for(i=0;i<rows.length;i++)
				if(rows[i].value==componentId)
					table.deleteRow(i);
			
			if (xml.responseText.indexOf('NOSUBMIT') != -1)
				document.getElementById('rsform_submit_button_msg').style.display = '';
			
			tidyOrder(true);
			
			stateDone();
		}
    }
	xml.open('GET','index.php?option=com_rsform&task=components.remove&ajax=1&cid[]='+componentId+'&formId='+formId+'&randomTime='+Math.random(),true);
	xml.send(null);
}

function processComponent(componentType)
{
	for (var i=0; i<document.getElementsByName('componentSaveButton').length; i++)
	{
		document.getElementsByName('componentSaveButton')[i].disabled = true;
	}
	
	if (isset(document.getElementById('rsformerror0')))
		document.getElementById('rsformerror0').style.display = 'none';
	if (isset(document.getElementById('rsformerror1')))
		document.getElementById('rsformerror1').style.display = 'none';
	if (isset(document.getElementById('rsformerror2')))
		document.getElementById('rsformerror2').style.display = 'none';
	if (isset(document.getElementById('rsformerror3')))
		document.getElementById('rsformerror3').style.display = 'none';
	
	stateLoading();
	
	xml=buildXmlHttp();

	var url = 'index.php?option=com_rsform&task=components.validate.name&randomTime=' + Math.random();
	xml.open("POST", url, true);
	params  = 'componentName=' + escape(document.getElementById('NAME').value);
	params += '&formId=' + document.getElementById('formId').value;
	params += '&currentComponentId=' + document.getElementById('componentIdToEdit').value;
	params += '&componentType=' + componentType;
	
	if (componentType == 9)
		params += '&destination=' + escape(document.getElementById('DESTINATION').value);
	if (componentType == 6)
	{
		params += '&mindate=' + escape(document.getElementById('MINDATE').value);
		params += '&maxdate=' + escape(document.getElementById('MAXDATE').value);
	}
	
	//Send the proper header information along with the request
	xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xml.setRequestHeader("Content-length", params.length);
	xml.setRequestHeader("Connection", "close");

	xml.send(params);
	xml.onreadystatechange=function()
    {
		if(xml.readyState==4)
		{
			if(xml.responseText.indexOf('Ok') == -1)
			{
				response = xml.responseText.split('|');
				
				RSFormPro.$('.rsform_secondarytabs li a').each(function(index,el) {
					RSFormPro.$(el).removeClass('active');
				});
				RSFormPro.$('#rsform_textboxdiv').formTabs(parseInt(response[0]));
				document.getElementById('rsformerror'+parseInt(response[0])).innerHTML = response[1];
				document.getElementById('rsformerror'+parseInt(response[0])).style.display = '';
				
				stateDone();
				
				for (var i=0; i<document.getElementsByName('componentSaveButton').length; i++)
				{
					document.getElementsByName('componentSaveButton')[i].disabled = false;
				}
			}
			else
				submitbutton('components.save');
		}
    }
}

function changeFormAutoGenerateLayout(formId, value)
{
	stateLoading();
	var layouts=document.getElementsByName('FormLayoutName');
	var layoutName='';
	for(i=0;i<layouts.length;i++)
		if(layouts[i].checked)
			layoutName=layouts[i].value

	xml=buildXmlHttp();
	xml.onreadystatechange=function()
	{
		if(xml.readyState==4)
		{
			if(value == 1)
			{
				document.getElementById('rsform_layout_msg').style.display = 'none';
				document.getElementById('formLayout').readOnly=true;
				if (typeof(window.codemirror_html) != 'undefined') window.codemirror_html.setOption('readOnly', true);
			}
			else
			{
				document.getElementById('rsform_layout_msg').style.display = '';
				document.getElementById('formLayout').readOnly=false;
				if (typeof(window.codemirror_html) != 'undefined') window.codemirror_html.setOption('readOnly', false);
			}

			stateDone();
		}
	}
	xml.open('GET','index.php?option=com_rsform&task=forms.changeAutoGenerateLayout&formId='+formId+'&randomTime='+Math.random()+'&formLayoutName='+layoutName,true);
	xml.send(null);
}

function generateLayout(formId,alert)
{
	if(alert!='no')
	{
		var answer=confirm("Pressing the 'Generate layout' button will ERASE your current layout. Are you sure you want to continue?");
		if(answer==false) return;
	}
	var layoutName = 'inline';
	for (var i = 0; i<document.getElementsByName('FormLayoutName').length; i++)
		if (document.getElementsByName('FormLayoutName')[i].checked)
			layoutName = document.getElementsByName('FormLayoutName')[i].value;

	stateLoading();
	xml=buildXmlHttp();
	xml.onreadystatechange=function()
	{
		if(xml.readyState==4)
		{
			document.getElementById('formLayout').value=xml.responseText;
			if (typeof(window.codemirror_html) != 'undefined') window.codemirror_html.setValue(xml.responseText);
			stateDone();
		}
	}
	xml.open('GET','index.php?option=com_rsform&task=layouts.generate&layoutName='+layoutName+'&formId='+formId+'&randomTime='+Math.random(),true);
	xml.send(null);
}

function saveLayoutName(formId,layoutName)
{
	for (var i=0; i<document.getElementsByName('ThemeName').length; i++)
		document.getElementsByName('ThemeName')[i].disabled = layoutName == 'responsive' ? true : false;
	document.getElementById('rsform_themes_disabled').style.display = layoutName == 'responsive' ? '' : 'none';
	
	stateLoading();
	xml=buildXmlHttp();
	xml.open('GET','index.php?option=com_rsform&task=layouts.save.name&formId='+formId+'&randomTime='+Math.random()+'&formLayoutName='+layoutName,true);
	xml.send(null);
	xml.onreadystatechange=function()
	{
		if(xml.readyState==4)
		{
			if(document.getElementById('FormLayoutAutogenerate1').checked==true)
				generateLayout(formId, 'no');
			stateDone();
		}
	}
	
	
}

function stateLoading()
{
	document.getElementById('state').style.display = '';
}

function stateDone()
{
	document.getElementById('state').style.display = 'none';
}
 
function refreshCaptcha(componentId, captchaPath)
{
	if(!captchaPath) captchaPath = 'index.php?option=com_rsform&task=captcha&componentId=' + componentId;
	document.getElementById('captcha' + componentId).src = captchaPath + '&' + Math.random();
	document.getElementById('captchaTxt' + componentId).value='';
	document.getElementById('captchaTxt' + componentId).focus();
}

function isset () {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: FremyCompany
    // +   improved by: Onno Marsman
    // +   improved by: Rafał Kukawski
    // *     example 1: isset( undefined, true);
    // *     returns 1: false
    // *     example 2: isset( 'Kevin van Zonneveld' );
    // *     returns 2: true
    var a = arguments,
        l = a.length,
        i = 0,
        undef;

    if (l === 0) {
        throw new Error('Empty isset');
    }

    while (i !== l) {
        if (a[i] === undef || a[i] === null) {
            return false;
        }
        i++;
    }
    return true;
}

function exportProcess(start, limit, total)
{
	xml=buildXmlHttp();
	xml.onreadystatechange=function()
    {
		if(xml.readyState==4)
		{
			post = xml.responseText;
			if(post.indexOf('END') != -1)
			{
				document.getElementById('backButtonContainer').style.display = '';
				document.getElementById('progressBar').style.width = document.getElementById('progressBar').innerHTML = '100%';
				document.location = 'index.php?option=com_rsform&task=submissions.export.file&ExportFile=' + document.getElementById('ExportFile').value + '&ExportType=' + document.getElementById('exportType').value;
			}
			else
			{
				document.getElementById('progressBar').style.width = Math.ceil(start*100/total) + '%';
				document.getElementById('progressBar').innerHTML = Math.ceil(start*100/total) + '%';
				start = start + limit;
				exportProcess(start, limit, total);
			}
		}
    }
		
	xml.open('GET','index.php?option=com_rsform&task=submissions.export.process&exportStart=' + start + '&exportLimit=' + limit + '&randomTime=' + Math.random(),true);
	xml.send(null);
}

function number_format(number, decimals, dec_point, thousands_sep)
{
    var n = number, prec = decimals;
    n = !isFinite(+n) ? 0 : +n;
    prec = !isFinite(+prec) ? 0 : Math.abs(prec);
    var sep = (typeof thousands_sep == "undefined") ? ',' : thousands_sep;
    var dec = (typeof dec_point == "undefined") ? '.' : dec_point;
 
    var s = (prec > 0) ? n.toFixed(prec) : Math.round(n).toFixed(prec); //fix for IE parseFloat(0.55).toFixed(0) = 0;
 
    var abs = Math.abs(n).toFixed(prec);
    var _, i;
 
    if (abs >= 1000) {
        _ = abs.split(/\D/);
        i = _[0].length % 3 || 3;
 
        _[0] = s.slice(0,i + (n < 0)) +
              _[0].slice(i).replace(/(\d{3})/g, sep+'$1');
 
        s = _.join(dec);
    } else {
        s = s.replace('.', dec);
    }
 
    return s;
}

function changeValidation(elem)
{
	if (elem == null) return;
	if(elem.id == 'VALIDATIONRULE')
	{
		if (document.getElementById('idVALIDATIONEXTRA'))
		{
			document.getElementById('captionVALIDATIONEXTRA').innerHTML = RStranslateText(elem.value == 'regex' ? 'regex' : 'extra');
			
			if(elem.value == 'custom' || elem.value == 'numeric' || elem.value == 'alphanumeric' || elem.value == 'alpha' || elem.value == 'regex')
				document.getElementById('idVALIDATIONEXTRA').className='showVALIDATIONEXTRA';
			else
				document.getElementById('idVALIDATIONEXTRA').className='hideVALIDATIONEXTRA';
		}
	}
}

function submissionChangeForm(formId)
{
	document.location = 'index.php?option=com_rsform&task=submissions.manage&formId=' + formId;
}

function toggleCustomizeColumns()
{
	var el = RSFormPro.$('#columnsDiv');
	
	if (el.is(':hidden'))
		el.slideDown('fast');
	else
		el.slideUp('fast');
}

function closeAllDropdowns(except)
{
	var dropdowns = RSFormPro.$('.dropdownContainer');
	var except 	  = RSFormPro.$('#dropdown' + except);
	
	for (var i=0; i<dropdowns.length; i++)
	{
		var dropdown = RSFormPro.$(dropdowns[i]).children('div');
		if (dropdown.attr('id') != except.attr('id'))
			RSFormPro.$(dropdowns[i]).children('div').hide();
	}
}

function toggleDropdown(what,extra)
{
	var name		= what.name;
	closeAllDropdowns(name);
	var nameid = name.replace(']', '').replace('[', '');
	//var parent		= RSFormPro.$('#' + nameid).parent();
	var quickfields = returnQuickFields();
	var parent = RSFormPro.$(what).parent();
	if (RSFormPro.$('#dropdown' + nameid).length == 0)
	{
		var divContainer = document.createElement('div');
		RSFormPro.$(divContainer).click(function(e) { e.stopPropagation(); e.preventDefault(); });
		divContainer.className = 'dropdownContainer';
		
		var divDropdown = document.createElement('div');
		divDropdown.id = 'dropdown' + nameid;
		divDropdown.setAttribute('id', 'dropdown' + nameid);
		divContainer.appendChild(divDropdown);
		
		if (extra)
		for (var j=0; j<extra.length; j++)
		{
			var a = document.createElement('a');
			a.innerHTML = '{' + extra[j] + '}';
			a.href = 'javascript: void(0);';
			a.onclick = function() { dropdownClick(nameid, this); };
			
			divDropdown.appendChild(a);
		}
		
		for (var i=0; i<quickfields.length; i++)
		{
			var a = document.createElement('a');
			a.innerHTML = '{' + quickfields[i] + ':value}';
			a.href = 'javascript: void(0);';
			a.onclick = function() { dropdownClick(nameid, this); };
			
			divDropdown.appendChild(a);
		}
		
		parent.append(divContainer);
	}
	
	var dropdown = RSFormPro.$('#dropdown' + nameid);
	
	if (dropdown.is(':hidden'))
		dropdown.slideDown('fast');
	else
		dropdown.slideUp('fast');
}

function dropdownClick(what, a)
{
	var input 	 = RSFormPro.$('#' + what);
	var dropdown = RSFormPro.$('#dropdown' + what);
	var value    = RSFormPro.$(a).html();
	
	if (input.val().replace(/^\s+|\s+$/g,'').length > 0)
	{
		input.val(input.val().replace(/^\s+|\s+$/g,''));
		if (input.val().substring(input.val().length - 1) != ',' && (what != 'AdminEmailFromName' && what != 'UserEmailFromName' && what != 'AdminEmailSubject' && what != 'UserEmailSubject'))
			value = input.val() + ',' + value;
		else
			value = input.val() + ' ' + value;
	}
		
	input.val(value);
	
	dropdown.slideUp('fast');
}

function toggleQuickAdd()
{
	var what = 'none';
	if (document.getElementById('QuickAdd1').style.display == 'none')
		what = '';
	
	document.getElementById('QuickAdd1').style.display = what;
	document.getElementById('QuickAdd2').style.display = what;
	document.getElementById('QuickAdd3').style.display = what;
	document.getElementById('QuickAdd4').style.display = what;
}

function mpConnect()
{
	var fields = RSFormPro.$("#tablers :input");
	var params = new Array();
	var fname = '';
	var fvalue = '';
	
	for (i=0; i < fields.length; i++)
	{
		if (fields[i].type == 'button') continue;
		
		if (fields[i].type == 'radio')
		{
			if (fields[i].checked)
			{
				if (fields[i].name == 'rsfpmapping[connection]')
				{
					fname  = 'connection';
					fvalue = fields[i].value;
				}
				
				if (fields[i].name == 'rsfpmapping[method]')
				{
					fname  = 'method';
					fvalue = fields[i].value;
				}
			} else continue;
		}
		
		fname  = fields[i].name;
		fvalue = fields[i].value;
		
		params.push(fname + '=' + encodeURIComponent(fvalue));
	}
	params.push('randomTime=' + Math.random());
	params = params.join('&');
	
	document.getElementById('mappingloader').style.display = '';
	
	xmlHttp = buildXmlHttp();
	xmlHttp.open("POST", 'index.php?option=com_rsform&task=gettables&controller=mappings', true);

	//Send the proper header information along with the request
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");

	xmlHttp.onreadystatechange = function() 
	{//Call a function when the state changes.
		if (xmlHttp.readyState==4)
		{
			response = xmlHttp.responseText.split('|');
			
			if (response[0] == 1)
			{
				document.getElementById('rsfpmappingContent').innerHTML = response[1];
				document.getElementById('mpConnectionOn').style.display = 'none'; 
				document.getElementById('mpConnectionOff').style.display = ''; 
				document.getElementById('mpMethodOn').style.display = 'none'; 
				document.getElementById('mpMethodOff').style.display = ''; 
				document.getElementById('mpHostOn').style.display = 'none';
				document.getElementById('mpHostOff').style.display = '';
				document.getElementById('mpPortOn').style.display = 'none';
				document.getElementById('mpUsernameOn').style.display = 'none'; 
				document.getElementById('mpUsernameOff').style.display = ''; 
				document.getElementById('mpPasswordOn').style.display = 'none'; 
				document.getElementById('mpPasswordOff').style.display = ''; 
				document.getElementById('mpDatabaseOn').style.display = 'none'; 
				document.getElementById('mpDatabaseOff').style.display = '';
				
				if (document.getElementById('connection0').checked) document.getElementById('mpConnectionOff').innerHTML = getLabelText('connection0');
				if (document.getElementById('connection1').checked) document.getElementById('mpConnectionOff').innerHTML = getLabelText('connection1');
				if (document.getElementById('method0').checked) document.getElementById('mpMethodOff').innerHTML = getLabelText('method0');
				if (document.getElementById('method1').checked) document.getElementById('mpMethodOff').innerHTML = getLabelText('method1');
				if (document.getElementById('method2').checked) document.getElementById('mpMethodOff').innerHTML = getLabelText('method2');
				document.getElementById('mpHostOff').innerHTML = document.getElementById('MappingHost').value + ':' + document.getElementById('MappingPort').value;
				document.getElementById('mpUsernameOff').innerHTML = document.getElementById('MappingUsername').value;
				document.getElementById('mpPasswordOff').innerHTML = document.getElementById('MappingPassword').value;
				document.getElementById('mpDatabaseOff').innerHTML = document.getElementById('MappingDatabase').value;
			} else 
			{
				document.getElementById('rsfpmappingContent').innerHTML = '<font color="red">'+response[0]+'</font>';
			}
			
			document.getElementById('mappingloader').style.display = 'none';
		}
	}
	xmlHttp.send(params);
}

function getLabelText(element)
{
	return RSFormPro.$('#' + element).parent().text();
	/*
	var el = document.getElementById(element);
	while(el.nextSibling && !(/label/i.test(el.nextSibling.tagName)))
		el = el.nextSibling;
	return el.nextSibling.innerHTML;
	*/
}


function mpColumns(table)
{
	var fields = RSFormPro.$("#tablers :input");
	var params = new Array();
	var fname = '';
	var fvalue = '';
	
	for (i=0; i < fields.length; i++)
	{
		if (fields[i].type == 'button') continue;
		
		if (fields[i].type == 'radio')
		{
			if (fields[i].checked)
			{
				if (fields[i].name == 'rsfpmapping[connection]')
				{
					fname  = 'connection';
					fvalue = fields[i].value;
				}
				
				if (fields[i].name == 'rsfpmapping[method]')
				{
					fname  = 'method';
					fvalue = fields[i].value;
				}
			} else continue;
		}
		
		fname  = fields[i].name;
		fvalue = fields[i].value;
		
		params.push(fname + '=' + encodeURIComponent(fvalue));
	}
	params.push('table=' + table);
	params.push('type=set');
	params.push('tmpl=component');
	params.push('randomTime=' + Math.random());
	params = params.join('&');
	
	$('mappingloader2').style.display = '';
	
	xmlHttp = buildXmlHttp();
	xmlHttp.open("POST", 'index.php?option=com_rsform&task=columns&controller=mappings', true);

	//Send the proper header information along with the request
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");

	xmlHttp.onreadystatechange = function() 
	{//Call a function when the state changes.
		if (xmlHttp.readyState==4)
		{			
			if ((isset(document.getElementById('method0')) && document.getElementById('method0').checked) || (isset(document.getElementById('method1')) && document.getElementById('method1').checked) || (isset(document.getElementById('method')) && document.getElementById('method').value == 0) || (isset(document.getElementById('method')) && document.getElementById('method').value == 1))
				$('rsfpmappingColumns').innerHTML = xmlHttp.responseText;
			$('mappingloader2').style.display = 'none';
			
			if ((isset(document.getElementById('method1')) && document.getElementById('method1').checked) || (isset(document.getElementById('method2')) && document.getElementById('method2').checked) || (isset(document.getElementById('method')) && document.getElementById('method').value == 1) || (isset(document.getElementById('method')) && document.getElementById('method').value == 2))
				mappingWhere(table);
		}
	}
	xmlHttp.send(params);
}

function mappingdelete(formid,mid)
{
	stateLoading();
	
	params = 'formId='+formid+'&mid='+mid+'&tmpl=component&randomTime=' + Math.random();
	
	xmlHttp = buildXmlHttp();
	xmlHttp.open("POST", 'index.php?option=com_rsform&task=remove&controller=mappings', true);

	//Send the proper header information along with the request
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");

	xmlHttp.onreadystatechange = function() 
	{//Call a function when the state changes.
		if (xmlHttp.readyState==4)
		{
			document.getElementById('mappingcontent').innerHTML = xmlHttp.responseText;
			stateDone();
			
			RSFormPro.$('#mappingTable tbody').tableDnD({
				onDragClass: 'rsform_dragged',
				onDrop: function (table, row) {
					tidyOrderMp(true);
				}
			});
			toggleOrderSpansMp();
		}
	}
	xmlHttp.send(params);
}

function ShowMappings(formid)
{
	stateLoading();
	
	params = 'formId='+formid+'&tmpl=component&randomTime=' + Math.random();
	
	xmlHttp = buildXmlHttp();
	xmlHttp.open("POST", 'index.php?option=com_rsform&task=showmappings&controller=mappings', true);

	//Send the proper header information along with the request
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");

	xmlHttp.onreadystatechange = function() 
	{//Call a function when the state changes.
		if (xmlHttp.readyState==4)
		{
			document.getElementById('mappingcontent').innerHTML = xmlHttp.responseText;
			stateDone();
			
			RSFormPro.$('#mappingTable tbody').tableDnD({
				onDragClass: 'rsform_dragged',
				onDrop: function (table, row) {
					tidyOrderMp(true);
				}
			});
			toggleOrderSpansMp();
		}
	}
	xmlHttp.send(params);
}

function mappingWhere(table)
{
	var fields = RSFormPro.$("#tablers :input");
	var params = new Array();
	var fname = '';
	var fvalue = '';
	
	for (i=0; i < fields.length; i++)
	{
		if (fields[i].type == 'button') continue;
		
		if (fields[i].type == 'radio')
		{
			if (fields[i].checked)
			{
				if (fields[i].name == 'rsfpmapping[connection]')
				{
					fname  = 'connection';
					fvalue = fields[i].value;
				}
				
				if (fields[i].name == 'rsfpmapping[method]')
				{
					fname  = 'method';
					fvalue = fields[i].value;
				}
			} else continue;
		}
		
		fname  = fields[i].name;
		fvalue = fields[i].value;
		
		params.push(fname + '=' + encodeURIComponent(fvalue));
	}
	params.push('table=' + table);
	params.push('type=where');
	params.push('tmpl=component');
	params.push('randomTime=' + Math.random());
	params = params.join('&');
	
	
	$('mappingloader2').style.display = '';
	
	xmlHttp = buildXmlHttp();
	xmlHttp.open("POST", 'index.php?option=com_rsform&task=columns&controller=mappings', true);

	//Send the proper header information along with the request
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");

	xmlHttp.onreadystatechange = function() 
	{//Call a function when the state changes.
		if (xmlHttp.readyState==4)
		{
			$('rsfpmappingWhere').innerHTML = xmlHttp.responseText;
			$('mappingloader2').style.display = 'none';
		}
	}
	xmlHttp.send(params);
}

function removeEmail(id,fid)
{
	stateLoading();
	
	var params = new Array();
	params.push('cid=' + id);
	params.push('formId=' + fid);
	params.push('tmpl=component');
	params.push('randomTime=' + Math.random());
	params = params.join('&');
	
	xmlHttp = buildXmlHttp();
	xmlHttp.open("POST", 'index.php?option=com_rsform&task=emails.remove', true);

	//Send the proper header information along with the request
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");

	xmlHttp.onreadystatechange = function() 
	{//Call a function when the state changes.
		if (xmlHttp.readyState==4)
		{
			stateDone();
			document.getElementById('emailscontent').innerHTML = xmlHttp.responseText;			
		}
	}
	xmlHttp.send(params);
}

function updateemails(fid)
{
	var content = document.getElementById('emailscontent');
	
	stateLoading();
	
	var params = new Array();
	params.push('formId=' + fid);
	params.push('tmpl=component');
	params.push('randomTime=' + Math.random());
	params = params.join('&');
	
	xmlHttp = buildXmlHttp();
	xmlHttp.open("POST", 'index.php?option=com_rsform&task=emails.update', true);

	//Send the proper header information along with the request
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");

	xmlHttp.onreadystatechange = function() 
	{//Call a function when the state changes.
		if (xmlHttp.readyState==4)
		{
			stateDone();
			content.innerHTML = xmlHttp.responseText;
		}
	}
	xmlHttp.send(params);
}

function initCodeMirror()
{
	if (typeof(CodeMirror) == 'undefined')
		return false;
	
	var codemirrors = new Array();
	codemirrors['js'] = RSFormPro.$('.codemirror-js');
	codemirrors['css'] = RSFormPro.$('.codemirror-css');
	codemirrors['php'] = RSFormPro.$('.codemirror-php');
	codemirrors['html'] = RSFormPro.$('.codemirror-html');
	
	// js
	for (var i=0; i<codemirrors['js'].length; i++)
	{
		CodeMirror.fromTextArea(codemirrors['js'][i], {
			lineNumbers: true,
			matchBrackets: true,
			mode: "text/javascript",
			indentUnit: 4,
			indentWithTabs: true,
			enterMode: "keep",
			tabMode: "shift"
		});
	}
	
	// css
	for (var i=0; i<codemirrors['css'].length; i++)
	{
		var editor = CodeMirror.fromTextArea(codemirrors['css'][i], {
			lineNumbers: true,
			matchBrackets: true,
			mode: "text/css",
			indentUnit: 4,
			indentWithTabs: true,
			enterMode: "keep",
			tabMode: "shift"
		});
	}
	
	// php
	for (var i=0; i<codemirrors['php'].length; i++)
	{
		CodeMirror.fromTextArea(codemirrors['php'][i], {
			lineNumbers: true,
			matchBrackets: true,
			mode: "application/x-httpd-php-open",
			indentUnit: 4,
			indentWithTabs: true,
			enterMode: "keep",
			tabMode: "shift"
		});
	}
	
	// html
	for (var i=0; i<codemirrors['html'].length; i++)
	{
		window.codemirror_html = CodeMirror.fromTextArea(codemirrors['html'][i], {
			lineNumbers: true,
			matchBrackets: true,
			mode: "text/html",
			indentUnit: 4,
			indentWithTabs: true,
			enterMode: "keep",
			tabMode: "shift",
			readOnly: RSFormPro.$(codemirrors['html'][i]).attr('readonly')
		});
	}
}

function conditionDelete(formid,cid)
{
	stateLoading();
	
	params = 'formId='+formid+'&cid='+cid+'&tmpl=component&randomTime=' + Math.random();
	
	xmlHttp = buildXmlHttp();
	xmlHttp.open("POST", 'index.php?option=com_rsform&task=remove&controller=conditions', true);

	//Send the proper header information along with the request
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");

	xmlHttp.onreadystatechange = function() 
	{//Call a function when the state changes.
		if (xmlHttp.readyState==4)
		{
			document.getElementById('conditionscontent').innerHTML = xmlHttp.responseText;
			stateDone();
		}
	}
	xmlHttp.send(params);
}

function showConditions(formid)
{
	stateLoading();
	
	params = 'formId='+formid+'&tmpl=component&randomTime=' + Math.random();
	
	xmlHttp = buildXmlHttp();
	xmlHttp.open("POST", 'index.php?option=com_rsform&task=showconditions&controller=conditions', true);

	//Send the proper header information along with the request
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");

	xmlHttp.onreadystatechange = function() 
	{//Call a function when the state changes.
		if (xmlHttp.readyState==4)
		{
			document.getElementById('conditionscontent').innerHTML = xmlHttp.responseText;
			stateDone();
		}
	}
	xmlHttp.send(params);
}

function openRSModal(href, type, size) {
	if (!type)
		type = 'Richtext';
	if (!size)
		size = '600x500';
	size = size.split('x');
	width = size[0];
	height = size[1];
	window.open(href, type, 'width=' + width + ', height=' + height + ',scrollbars=1');
}

RSFormPro.$(document).ready(initCodeMirror);
RSFormPro.$(document).ready(initRSFormPro);