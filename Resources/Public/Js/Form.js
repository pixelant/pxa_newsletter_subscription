"use strict";

const PxaNewsLetterSubscription = function (formSelector, callbackOnAjaxResponse) {
	this.alertWrapper = '.alert';
	this.hiddenClass = 'hidden';

	this.formSelector = formSelector;
	this.callbackOnAjaxResponse = callbackOnAjaxResponse || false;
};

PxaNewsLetterSubscription.prototype = {
	/**
	 * Form DOM
	 */
	forms: null,

	/**
	 * Init everything
	 */
	init: function () {
		let self = this;
		this.forms = document.querySelectorAll(this.formSelector);

		// If forms were found
		if (this.forms.length > 0) {
			[].forEach.call(this.forms, function (form) {
				form.addEventListener('submit', function (e) {
					e.preventDefault();

					self._ajaxRequest(this);
				}, true);

				// Accept terms?
				let requireAcceptTerms = form.dataset['requireAcceptTerms'] || false;
				if (requireAcceptTerms) {
					self._acceptTermsActions(form);
				}
			});
		}
	},

	/**
	 * Actions related to accept terms
	 *
	 * @param form
	 * @private
	 */
	_acceptTermsActions: function (form) {
		let submit = form.querySelector('[type="submit"]'),
			checkbox = form.querySelector('[type="checkbox"][name="tx_pxanewslettersubscription_subscription[subscription][acceptTerms]"]');

		checkbox.addEventListener('change', function (e) {
			submit.disabled = !checkbox.checked;
		});

		submit.disabled = true;

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

		// Disable submit button
		let submit = form.querySelector('[type="submit"]');
		submit.disabled = true;

		// Reset errors
		this._resetError(form);

		let xmlHttpRequest = new XMLHttpRequest();
		xmlHttpRequest.responseType = 'json';

		// Define what happens on successful data submission
		xmlHttpRequest.addEventListener('load', () => {
			if (!this._isFunction(this.callbackOnAjaxResponse)) {
				// If success
				if (xmlHttpRequest.status === 200 && xmlHttpRequest.response.success) {
					let successElement = document.createElement('div');

					successElement.className = 'alert alert-success';
					successElement.innerHTML = xmlHttpRequest.response.message;

					form.parentNode.replaceChild(successElement, form);
				} else {
					this._processRequestValidationErrors(form, xmlHttpRequest.response.errors || {})
				}

				submit.disabled = false;

				return;
			}

			// Custom function
			this._customResponseProcessing(xmlHttpRequest, form);
		});

		// Define what happens in case of error
		xmlHttpRequest.addEventListener('error', () => {
			let error = {error: 'Error occurred while receiving the document.'};

			if (!this._isFunction(this.callbackOnAjaxResponse)) {
				this._addError(form, error);

				return;
			}

			// Custom function
			this._customResponseProcessing(xmlHttpRequest, form);
		});

		// Set up our request
		xmlHttpRequest.open('POST', url);

		// Send our FormData object; HTTP headers are set automatically
		xmlHttpRequest.send(formData);
	},

	/**
	 * Go through all errors and show
	 *
	 * @param form
	 * @param errors
	 * @private
	 */
	_processRequestValidationErrors: function (form, errors) {
		for (let propertyName in errors) {
			if (!errors.hasOwnProperty(propertyName)) {
				continue;
			}

			let error = errors[propertyName];

			if (typeof error === 'object') {
				this._processRequestValidationErrors(form, error)
			} else {
				this._addError(form, error);
			}
		}
	},

	/**
	 * Reset errors
	 *
	 * @param form
	 * @private
	 */
	_resetError: function (form) {
		let alertWrapper = form.querySelector(this.alertWrapper);

		alertWrapper.innerHTML = '';
		alertWrapper.classList.add(this.hiddenClass);
	},

	/**
	 * Add error message to container
	 *
	 * @param form
	 * @param error
	 * @private
	 */
	_addError: function (form, error) {
		let alertWrapper = form.querySelector(this.alertWrapper);

		alertWrapper.innerHTML += error;

		alertWrapper.classList.remove(this.hiddenClass);
	},

	/**
	 * Process response with given callback
	 *
	 * @param response
	 * @param form
	 * @private
	 */
	_customResponseProcessing: function (response, form) {
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
