# Newsletter subscription

## Pixelant Newsletter subscription `pxa_newsletter_subscription`

This is an extension that makes it possible for a user to subscribe to a newsletter and being created as a frontend user or tt_address dataset.

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
