function lookupLastName(textId, callId, suggestionBox, suggestionList, formName, formId, formSuggest) 
{	//operates the autocomplete for a textbox
	if(textId.length == 0) 
	{
		// Hide the suggestion box.
		suggestionBox.hide();
	}
	else 
	{
		$.post("lastNameLookup.php", {queryString: ""+textId+"",
				call: ""+callId+"",	
				formName: ""+formName+"",
				formId: ""+formId+"", 
				formSuggest: ""+formSuggest+""}, 
			function(data)
		{
			if(data.length >0)
			{
			suggestionBox.show();
			suggestionList.html(data);
			}
		});
	}
} // lookup

function fillNames(thisValue, thatValue, formName, id, suggest) 
{	//fills in the value when an autocomplete value is selected
		
	//$('#name_payer').val(thisValue);
	formName.val(thisValue);
	//$('#id_payer').val(thatValue);
	id.val(thatValue);
	//setTimeout("$('#payer_suggestions').hide();", 200);
	suggest.hide().delay(200);
}

function cb_hide_show(elementId, showhide)
{	//a checkbox is used to hide or show an element
	//initial state of element should match initial state of checkbox
	if (elementId.checked==true)
	{
		showhide.show();
	}
	else
	{
		showhide.hide();
	}
}

function dd_hide_show(elementId, showhide, match)
{	//a dropdown list shows showhide when match is selected
	if (elementId==match)
	{
		showhide.show();
	}
	else
	{
		showhide.hide();
	}
}
