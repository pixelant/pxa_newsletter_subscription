(function (w, $) {
	var formElement = $('form[data-form="pxa-newsletter-subscription-form"]'),
		submitElement = $('[data-identifier="submit"]', formElement),
		spinnerElement = $('[data-identifier="spinner"]', formElement);

	formElement.on('submit',function(e) {
		e.preventDefault();

		// Disable button, and fade in spinner
		spinnerElement.fadeIn(50);
		submitElement.attr('disabled', 'disabled');

		$.ajax({
			//type of receiving data
			type: 'POST',
			url: formElement.attr('action'),
			data: formElement.serialize(),
			dataType: 'JSON',
			// if call is ok
			success: function(response) {
				//ajax sends msg from php, which informs user, what has happens
				var message = $('<div/>', {
					class: 'alert js__ajax-response',
					text: response.message
				});
				formElement.after(message);

				if (response.success) {
					// display message
					message.addClass('alert-success');
					// hide form
					formElement.hide();
				} else {
					// display message and set message to disapear after 5 sec.
					message.addClass('alert-danger').delay(5000).fadeOut('slow');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				// Set message and set it to disapear after 5 sec.
				var message = $('<div/>', {
					class: 'alert alert-danger js__ajax-response',
					text: textStatus
				});
				formElement.after(message);
				message.delay(5000).fadeOut('slow');
			},
			always: function() {
				// Hide spinner and enable inputs again
				spinnerElement.fadeOut(50);
				submitElement.removeAttr('disabled');
			}
		}); //end ajax
	}); //end submit

})(window, jQuery);