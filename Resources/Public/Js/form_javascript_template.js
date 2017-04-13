$(document).ready(function() {

	// $(document).acknowledgeinput({
	// 	default_state: 'hidden'
	//});

	var formElement = $('#__FORMNAME__');
	var submitElement = $('[data-identifier="submit"]', formElement);
	var spinnerElement = $('[data-identifier="spinner"]', formElement);

	submitElement.click(function(e) {
		e.preventDefault();

		// get form data and also include the input that was pressed
		var formData = $(this).closest('form').serializeArray();
		formData.push({ name: this.name, value: this.value });

		// create a uniqe tag id for temporary message
		var ts = Math.round(+new Date());
		var messageTagId = 'contact-form-message' + ts;

		// Disable button, and fade in spinner
		spinnerElement.fadeIn(50);
		submitElement.attr('disabled', 'disabled');

		$.ajax({
			//type of receiving data
			type: 'POST',

			//page where ajax is running
			url: formElement.attr('action'),

			//send_data, data which will be send to php
			data: formData,
			dataType: "JSON",
			// if call is ok
			success: function(response) {
				//ajax sends msg from php, which informs user, what has happens
				formElement.after('<div id="' + messageTagId + '" class="alert">' + response.message + '<div>');
				if (response.success) {
					// display message
					$('#' + messageTagId).addClass('alert-success');
					// hide form
					formElement.hide();
				} else {
					// display message and set message to disapear after 5 sec.
					$('#' + messageTagId).addClass('alert-danger').delay(5000).fadeOut('slow');
				}
				// Hide spinner and enable inputs again
				spinnerElement.fadeOut(50);
				submitElement.removeAttr('disabled');
			},

			error: function(jqXHR, textStatus, errorThrown) {
				// Set message and set it to disapear after 5 sec.
				formElement.after('<div id="' + messageTagId + '" class="alert alert-danger">' + jqXHR_error_message + '<div>');
				$('#' + messageTagId).delay(5000).fadeOut('slow');
				// hide spinner and enable inputs again
				spinnerElement.fadeOut(50);
				submitElement.removeAttr('disabled');
			}
		}); //end ajax
	}); //end submit

});