# !!! This repo is obosolete use https://github.com/pixelant/pxa_newsletter_subscription instead !!!

# Pixelant

[![pxa_newsletter_subscription](https://img.shields.io/badge/pxa_newsletter_subscription-4.0.0-green.svg?style=flat-square)](https://bitbucket.org/pixelant/pxa_newsletter_subscription) [![TYPO3](https://img.shields.io/badge/TYPO3-7.6.0-orange.svg?style=flat-square)](https://typo3.org/)

## Newsletter subscription [pxa_newsletter_subscription](https://bitbucket.org/pixelant/pxa_newsletter_subscription)
This is an extension that makes it possible for a user to subscribe to a newsletter and being created as a frontend user.

## Signal slot

After a user have been created there is a signal slot that is called:

    afterFeUserCreation

Which can be used for manipulating the user.

## Changelog

* Added typoscript settings that can modifify output from templates (class wraps).
* Possibility to send unsubscribe mail.
* Changed localization to xliff.
* Texts in mail via flexform.
* Email config via flexform (from name, from email and reply-to).
* Name field in frontend is optional, configured in flexform.
* Option to set confirmation page in flexform.
* Changed rootPaths
* Multiple registrations on same page.
* Controller uses namespace.
