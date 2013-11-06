// shoe therapy


$(function() {
    $('#refcode').click
       (
       function()
          {

              $(this).focus().select();

          }
         );
       });


function expand(id)
{
	var obj = document.getElementById(id);
	obj.style.height="210px";
}

function contract(id)
{
	var obj = document.getElementById(id);
	obj.style.height="0px";
}

// gift card information
function expand_info(id)
{
	var obj = document.getElementById(id);
	obj.style.height="360px";
}

function contract_info(id)
{
	var obj = document.getElementById(id);
	obj.style.height="235px";
}

// show/hide tabs
function toggle_tabs(row_base_id,row_show_id,rows)
{
	show_row_id = row_base_id + "_product_" + row_show_id;
	obj_show = document.getElementById(show_row_id);
	for(i=0; i<=rows; i++) {
		row_id = row_base_id + "_product_" + i;
		obj = document.getElementById(row_id);
		row_id2 = row_base_id + "_button_" + i;
		obj2 = document.getElementById(row_id2);
		if (row_id == show_row_id) {
			obj.style.display='';
			obj2.className='tab_on';
		}
		else {
			obj.style.display='none';
			obj2.className='tab';
		}
	}
}

function toggle_tabs_boutique(row_base_id,row_show_id,start_row,rows)
{
	show_row_id = row_base_id + "_product_" + row_show_id;
	obj_show = document.getElementById(show_row_id);
	for(i=start_row; i<=rows; i++) {
		row_id = row_base_id + "_product_" + i;
		obj = document.getElementById(row_id);
		row_id2 = row_base_id + "_button_" + i;
		obj2 = document.getElementById(row_id2);
		if (row_id == show_row_id) {
			obj.style.display='';
			//obj2.className='tab_on';
		}
		else {
			obj.style.display='none';
			//obj2.className='tab';
		}
	}
}

// show/hide tabs - special case in product detail
function toggle_tabs2(row_base_id,row_show_id,rows)
{
	show_row_id = row_base_id + "_product_" + row_show_id;
	obj_show = document.getElementById(show_row_id);
	for(i=0; i<=rows; i++) {
		row_id = row_base_id + "_product_" + i;
		obj = document.getElementById(row_id);
		row_id2 = row_base_id + "_button_" + i;
		obj2 = document.getElementById(row_id2);
		if (row_id == show_row_id) {
			obj.style.display='';
			obj2.className='tab_on';
		}
	}
}

// show/hide star rating
function toggle_rating(row_base_id,row_show_id,rows,formField,formFieldValue,iteratorStart)
{
	show_row_id = row_base_id + "_product_" + row_show_id;
	obj_show = document.getElementById(show_row_id);
	ff = document.getElementById(formField);
	for(i=iteratorStart; i<=rows; i++) {
		row_id = row_base_id + "_product_" + i;
		obj = document.getElementById(row_id);
		row_id2 = row_base_id + "_star_" + i;
		obj2 = document.getElementById(row_id2);
		if (row_id == show_row_id) {
			obj2.className='tab_on';
			ff.value = formFieldValue;
		}
		else {
			obj2.className='tab';
		}
	}
}

// show/hide star rating in boutique
function toggle_rating_boutique(row_base_id,row_show_id,rows,formField,formFieldValue,iteratorStart)
{
	show_row_id = row_base_id + "_product_" + row_show_id;
	obj_show = document.getElementById(show_row_id);
	ff = document.getElementById(formField);
	for(i=iteratorStart; i<=rows; i++) {
		row_id = row_base_id + "_product_" + i;
		obj = document.getElementById(row_id);
		row_id2 = row_base_id + "_star_" + i;
		obj2 = document.getElementById(row_id2);
		if (row_id == show_row_id) {
			obj2.className='tab_on';
			ff.value = formFieldValue;
		}
		else {
			obj2.className='tab';
		}
	}
}

// show/hide rows
function toggle_rows(row_base_id,row_show_id,rows)
{
	show_row_id = row_base_id + "_" + row_show_id;
	obj_show = document.getElementById(show_row_id);
	for(i=1; i<=rows; i++) {
		row_id = row_base_id + "_" + i;
		obj = document.getElementById(row_id);
		if (row_id == show_row_id) {
			obj.style.display='';
		}
		else {
			try {
				obj.style.display = 'none';
			}
			catch(err){}
		}
	}
}

// switch the show/hide state of two IDs
function toggle_switch(show_id,hide_id) {
	document.getElementById(show_id).style.display = 'none';
	document.getElementById(hide_id).style.display = '';
}

function toggle_on(id)
{
	var obj = document.getElementById(id);
	obj.style.display='';
}

function toggle_off(id)
{
	var obj = document.getElementById(id);
	obj.style.display='none';
}


// Simple show/hide
function toggle_simple(id)
{
	var obj = document.getElementById(id);
	if (obj.style.display == '')
	{
		obj.style.display='none';
	}
	else
	{
		obj.style.display='';
	}
}

// ?
function toggle(id,status)
{
	var obj = document.getElementById(id);
	if (obj.style.display == '' && status == 'off')
	{
		obj.style.display='none';
	}
	else if (obj.style.display == 'none' && status == 'on')
	{
		obj.style.display='';
	}
}

// Used on the contact us form
function check_subject() {
	subject_id = document.getElementById('destination');
	subject_value = subject_id.options[subject_id.selectedIndex].value;
	if (subject_value == '4') {
		alert("Please note: Return requests cannot be accepted via email at this time. \nPlease contact us by phone (listed above).");
		subject_id.selectedIndex = 0;
	}
}

// Clear form's default form value
function clearText(thefield){
	if (thefield.defaultValue==thefield.value) {
		thefield.value = ""
	}
}

function changeBox(){
    document.getElementById('div1').style.display='none';
    document.getElementById('div2').style.display='';
    document.getElementById('verification_data').focus();
}

function restoreBox(){
    if(document.getElementById('verification_data').value=='')
    {
      document.getElementById('div1').style.display='';
      document.getElementById('div2').style.display='none';
    }
}

function changeBox10(){
    document.getElementById('div10').style.display='none';
    document.getElementById('div20').style.display='';
    document.getElementById('password2').focus();
}
function restoreBox10(){
    if(document.getElementById('password2').value=='')
    {
      document.getElementById('div10').style.display='';
      document.getElementById('div20').style.display='none';
    }
}


Global = {
	FixPng: function( img ){
		if(document.all){
			img.parentNode.style.width = img.offsetWidth;
			img.parentNode.style.height = img.offsetHeight;
			img.parentNode.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale src='"+ img.src +"')"
		} else {
			img.style.visibility = "visible"
		}
	}
}

function isValidDate(dateStr)
{
	var datePat = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
	var matchArray = dateStr.match(datePat); // is the format ok?

	if (matchArray == null)
	{
		alert("Please enter date as either mm/dd/yyyy or mm-dd-yyyy.");
		return false;
	}

	month = matchArray[1]; // parse date into variables
	day = matchArray[3];
	year = matchArray[5];

	if (month < 1 || month > 12)
	{
		// check month range
		alert("Month must be between 1 and 12.");
		return false;
	}

	if (day < 1 || day > 31)
	{
		alert("Day must be between 1 and 31.");
		return false;
	}

	if ((month==4 || month==6 || month==9 || month==11) && day==31)
	{
		alert("Month "+month+" doesn't have 31 days!");
		return false;
	}

	if (month == 2)
	{
		// check for february 29th
		var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
		if (day > 29 || (day==29 && !isleap))
		{
			alert("February " + year + " doesn't have " + day + " days!");
			return false;
		}
	}
	return true; // date is valid
}

function set_form_product_ids (master_product_id, related_master_product_id, product_id) {

	$("#goproduct_" + master_product_id + " input[name='master_product_id']").val(related_master_product_id);
	$("#goproduct_" + master_product_id + " input[name='product_id']").val(product_id);
	toggle_stock_status(master_product_id, related_master_product_id);
}

function toggle_related_thumbs (master_product_id, related_master_product_id) {
	$("#product_" + master_product_id + " .product_images").hide();
	$("#product_" + master_product_id + " #swatch_shoe_product_" + related_master_product_id).show();

}

function toggle_stock_status (master_product_id, related_master_product_id) {
	$("#stockstatus_wrapper_" + master_product_id + " .stockstatus").hide();
	$("#stockstatus_wrapper_" + master_product_id + " #stockstatus_" + related_master_product_id).show();
}

function swatch_clicked(master_product_id, related_master_product_id, product_id) {
	set_form_product_ids(master_product_id, related_master_product_id, product_id);
	toggle_related_thumbs(master_product_id, related_master_product_id);
}




function check_option_answers (optionType) {
	//alert($(".option." + optionType + " input[name=" + optionType + "_membership_plan_recommendation_request_field]").is(":checked"));
	var answer = $(".option." + optionType + " input[name=" + optionType + "_membership_plan_recommendation_request_field]").is(":checked");
	if (!answer)
		$("#error_" + optionType).show();
	return answer;
}

function publish_fb (user_message, caption, name, description, image_src, href_url, object_type, link_text) {
	object_type_save = object_type;


	 FB.ui(
	   {
	     method: 'stream.publish',
	     message: user_message,
	     attachment: {
	       name: name,
	       caption: caption,
	       description: (
	         description
	       ),
	       href: href_url,
		   media:[{'type':'image','src':image_src,'href':href_url}]
	     },
	     action_links: [
	       {text: link_text, href: href_url}
	     ],
	     user_message_prompt: 'What\'s on your mind?'
	   },
	   record_publish_callback
	 );

}

function record_publish_callback (response) {
		if(response && response.post_id) {
			record_publish();
		}
}

function show_rating_box (div, master_product_id, product_category_id) {
	$('.rating').hide();
	$(div).html($('#rating_loading').html()).show();
	$(div).load('index.cfm?action=shop.view_rating_widget&product_category_id=' + product_category_id + '&master_product_id=' + master_product_id + '&uid=' + Math.floor(Math.random()*1000));
}

function close_rating_box () {
	$('.rating').hide();

}

function record_publish () {
	pageTracker._trackEvent('social', 'share', object_type_save);
}


