(function (w, $) {
	var submitElement = $('[data-identifier="submit"]', 'form[data-form="pxa-newsletter-subscription-form"]');

	submitElement.on('click', function (e) {
		e.preventDefault();

		var currentButton = $(this),
			formElement = currentButton.parents('form[data-form="pxa-newsletter-subscription-form"]'),
			spinnerElement = $('[data-identifier="spinner"]', formElement),
			data = formElement.serializeArray();

		data.push({name: currentButton.attr('name'), value: 1});

		// Disable button, and fade in spinner
		spinnerElement.fadeIn(50);
		currentButton.prop('disabled', true);

		$.ajax({
			//type of receiving data
			type: 'POST',
			url: formElement.attr('action'),
			data: data,
			dataType: 'JSON'
		}).done(function (response) {
			//ajax sends msg from php, which informs user, what has happens
			var message = $('<div/>', {
				class: 'alert js__ajax-response',
				text: response.message
			});
			formElement.after(message);

			if (response.success) {
				if(response.redirect != '') {
					$(location).attr('href', response.redirect);
				} else {
					// display message
					message.addClass('alert-success');
					// hide form
					formElement.hide();
				}
			} else {
				// display message and set message to disapear after 5 sec.
				message.addClass('alert-danger').delay(5000).fadeOut('slow');
			}
		}).fail(function (jqXHR, textStatus) {
			// Set message and set it to disapear after 5 sec.
			var message = $('<div/>', {
				class: 'alert alert-danger js__ajax-response',
				text: textStatus
			});
			formElement.after(message);
			message.delay(5000).fadeOut('slow');
		}).always(function () {
			// Hide spinner and enable inputs again
			spinnerElement.fadeOut(50);
			currentButton.prop('disabled', false);
		}); //end ajax
	}); //end submit

})(window, jQuery);