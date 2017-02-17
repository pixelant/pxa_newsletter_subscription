$(document).ready(function() {

	// $(document).acknowledgeinput({
	// 	default_state: 'hidden'
	//});

	$("#__FORMNAME__ input[type=submit]").click(function(e) {
		
		e.preventDefault();
  		
  		// get form data and also include the input that was pressed
  		var formData = $(this).closest('form').serializeArray();
  		formData.push({ name: this.name, value: this.value });
		
		// create a uniqe tag id for temporary message
		var ts = Math.round(+new Date());
		var messageTagId = 'contact-form-message' + ts;
		
		// Disable button, and fade in i tag inside
		$('#__FORMNAME__ input[type="submit"] i').fadeIn(50);
		$('#__FORMNAME__ input[type="submit"]').attr('disabled', 'disabled');
		
		$.ajax({
			//type of receiving data
			type: 'POST',
			
			//page where ajax is running
			url: $('#__FORMNAME__').attr('action') + '?type=6171240&tx_pxanewslettersubscription_subscription%5Baction%5D=ajax&L=' + ajaxLanguageId,

			//send_data, data which will be send to php
			data: formData,
			dataType: "JSON",
			// if call is ok
			success: function(response) {
				//ajax sends msg from php, which informs user, what has happens
				$('#__FORMNAME__').after('<div id="' + messageTagId + '" class="alert">' + response.message + '<div>');
				if (response.success) {
					// display message
					$('#' + messageTagId).addClass('alert-success');
					// hide form
					$('#__FORMNAME__').hide();
				} else {
					// display message
					$('#' + messageTagId).addClass('alert-danger');
					// Set message to disapear after 5 sec.
					$('#' + messageTagId).delay(5000).fadeOut('slow');
				}
				// Hide i tag (spinner) and enable inputs again
				$('#__FORMNAME__ input[type="submit"] i').fadeOut(50);
				$('#__FORMNAME__ input[type="submit"]').removeAttr('disabled');
			},

			error: function(jqXHR, textStatus, errorThrown) {
				// Set message and set it to disapear after 5 sec.
				$('#__FORMNAME__').after('<div id="' + messageTagId + '" class="alert alert-danger">' + jqXHR_error_message + '<div>');
				$('#' + messageTagId).delay(5000).fadeOut('slow');
				// Hide spinner and enable inputs again
				$('#__FORMNAME__ input[type="submit"] i').fadeOut(50);
				$('#__FORMNAME__ input[type="submit"]').removeAttr('disabled');
			}
		}); //end ajax
	}); //end submit

});
