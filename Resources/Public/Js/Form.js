"use strict";

const PxaNewsLetterSubscription = function (formSelector, callbackOnAjaxResponse) {
	this.formSelector = formSelector;
	this.callbackOnAjaxResponse = callbackOnAjaxResponse || false;
};

PxaNewsLetterSubscription.prototype = {
	/**
	 * Form DOM
	 */
	form: null,

	/**
	 * Init everything
	 */
	init: function () {
		let self = this;
		this.form = document.querySelector(this.formSelector);

		// If forms were found
		if (this.form.length > 0) {
			this.form.addEventListener('submit', function (e) {
				/*e.preventDefault();

				self._ajaxRequest(this);*/
			}, true);
		}
	},

	/**
	 * Send form with ajax
	 *
	 * @param form
	 * @private
	 */
	_ajaxRequest: function (form) {
		let formData = new FormData(form),
			url = form.action;

		let xmlHttpRequest = new XMLHttpRequest();

		// Define what happens on successful data submission
		xmlHttpRequest.addEventListener('load', (response) => {
			if (!this._isFunction(this.callbackOnAjaxResponse)) {
				console.log(response);

				return;
			}

			// Custom function
			this._customResponseProcessing(response, form);
		});

		// Define what happens in case of error
		xmlHttpRequest.addEventListener('error', (response) => {
			if (!this._isFunction(this.callbackOnAjaxResponse)) {
				console.log(response);

				return;
			}

			// Custom function
			this._customResponseProcessing(response, form);
		});

		// Set up our request
		xmlHttpRequest.open('POST', url);

		// Send our FormData object; HTTP headers are set automatically
		xmlHttpRequest.send(formData);
	},

	/**
	 * Process response with given callback
	 *
	 * @param response
	 * @param form
	 * @private
	 */
	_customResponseProcessing: function(response, form) {
		this.callbackOnAjaxResponse({
			response: response,
			form: form
		});
	},

	/**
	 *
	 * @param func
	 * @returns {boolean}
	 * @private
	 */
	_isFunction: function (func) {
		return typeof func === 'function';
	}
};

(new PxaNewsLetterSubscription('[data-form="pxa-newsletter-subscription-form"]')).init();
