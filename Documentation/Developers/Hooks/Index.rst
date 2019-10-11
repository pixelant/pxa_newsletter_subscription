.. include:: ../../Includes.txt


.. _hooks:

Available hooks
^^^^^^^^^^^^^^^

There are several Signals/Slots implemented.
https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Hooks/Concept/Index.html

**Before subscription validation**

Hook is executed before subscription validation

- *\\Pixelant\\PxaNewsletterSubscription\\Domain\\Validator\\SubscriptionValidator* - class name
- *beforeSubscriptionValidation* - signal name
- arguments - subscription and settings

**Before persist subscription**

Hook is executed after validation and before subscription is persist to database.

- *\\Pixelant\\PxaNewsletterSubscription\\Controller\\AjaxController* - class name
- *beforePersistSubscription* - signal name
- arguments - subscription and settings

**After persist subscription**

Hook is executed after subscription was persist to database.

- *\\Pixelant\\PxaNewsletterSubscription\\Controller\\AjaxController* - class name
- *afterPersistSubscription* - signal name
- arguments - subscription and settings

**Before confirm subscription**

Hook is executed before subscription is confirmed by user, after click link in email

- *\\Pixelant\\PxaNewsletterSubscription\\Controller\\NewsletterSubscriptionController* - class name
- *beforeConfirmSubscription* - signal name
- arguments - subscription, hash and settings

**Unsubscribe request**

Hook is executed when user try to unsubscribe and confirmation email was sent

- *\\Pixelant\\PxaNewsletterSubscription\\Controller\\NewsletterSubscriptionController* - class name
- *unsubscribeRequest* - signal name
- arguments - subscription

**Unsubscribe**

Hook is executed right before user subscription is removed

- *\\Pixelant\\PxaNewsletterSubscription\\Controller\\NewsletterSubscriptionController* - class name
- *unsubscribe* - signal name
- arguments - subscription

**Before build unsubscribe url**

Hook is executed before generation unsubscribe url

- *\\Pixelant\\PxaNewsletterSubscription\\Url\\SubscriptionUrlGenerator* - class name
- *beforeBuildUrlUnsubscribe* - signal name
- arguments - URL arguments

**Before build confirmations urls**

Hook is executed before generation user confirmation url and unsubscribe url

- *\\Pixelant\\PxaNewsletterSubscription\\Url\\SubscriptionUrlGenerator* - class name
- *beforeBuildUrlconfirm* - signal name confirmation url
- *beforeBuildUrlunsubscribeConfirm* - signal name unsubscribe confirmation url
- arguments - URL arguments
