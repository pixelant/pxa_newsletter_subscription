.. include:: ../../Includes.txt


.. _hooks:

Available hooks
^^^^^^^^^^^^^^^

Several Signals/Slots have been implemented.
https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Hooks/Concept/Index.html

**Before subscription validation**

Executed before subscription validation

- *\\Pixelant\\PxaNewsletterSubscription\\Domain\\Validator\\SubscriptionValidator* - class name
- *beforeSubscriptionValidation* - signal name
- arguments - subscription and settings

**Before persist subscription**

Executed after validation and before the subscription is persisted.

- *\\Pixelant\\PxaNewsletterSubscription\\Controller\\AjaxController* - class name
- *beforePersistSubscription* - signal name
- arguments - subscription and settings

**After persist subscription**

Executed after the subscription was persisted.

- *\\Pixelant\\PxaNewsletterSubscription\\Controller\\AjaxController* - class name
- *afterPersistSubscription* - signal name
- arguments - subscription and settings

**Before confirm subscription**

Executed before subscription is confirmed by user, after the email link

- *\\Pixelant\\PxaNewsletterSubscription\\Controller\\NewsletterSubscriptionController* - class name
- *beforeConfirmSubscription* - signal name
- arguments - subscription, hash and settings

**Unsubscribe request**

Executed when the user attempts to unsubscribe and confirmation email

- *\\Pixelant\\PxaNewsletterSubscription\\Controller\\NewsletterSubscriptionController* - class name
- *unsubscribeRequest* - signal name
- arguments - subscription

**Unsubscribe**

Executed right before a subscription is removed

- *\\Pixelant\\PxaNewsletterSubscription\\Controller\\NewsletterSubscriptionController* - class name
- *unsubscribe* - signal name
- arguments - subscription

**Before building the unsubscribe URL**

Hook is executed before generation unsubscribe url

- *\\Pixelant\\PxaNewsletterSubscription\\Url\\SubscriptionUrlGenerator* - class name
- *beforeBuildUrlUnsubscribe* - signal name
- arguments - URL arguments

**Before building confirmations URLs**

Executed before the user confirmation and unsubscribe URLs are generated

- *\\Pixelant\\PxaNewsletterSubscription\\Url\\SubscriptionUrlGenerator* - class name
- *beforeBuildUrlconfirm* - signal name confirmation url
- *beforeBuildUrlunsubscribeConfirm* - signal name unsubscribe confirmation url
- arguments - URL arguments
