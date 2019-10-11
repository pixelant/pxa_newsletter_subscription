.. include:: ../../Includes.txt


.. _email-templates:

Email notifications templates
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

It's possible to use custom email templates same as regular controller templates

Set new paths to templates

.. code-block:: typoscript

    plugin.tx_pxanewslettersubscription {
        view {
            templateRootPaths {
                50 = EXT:custom_extension_name/Resources/Private/Templates/
            }

            partialRootPaths {
                50 = EXT:custom_extension_name/Resources/Private/Partials/
            }

            layoutRootPaths {
                50 = EXT:custom_extension_name/Resources/Private/Layouts/
            }
        }
    }


After templates path was set you can now create new templates.

Email notifications templates list:

- AdminNewSubscription.html - Admin email after user subscription is finished
- AdminUnsubscribe.html - Admin email after user unsubscribed
- SubscribeConfirmation.html - Email to subscriber with confirmation link
- UserSuccessSubscription.html - Email to subscriber with notification about successful subscription
- UserUnsubscribeConfirmation.html - Email to subscriber with unsubscribe confirmation link

.. tip::

    According to TypoScript example you should put custom templates under folder:
    **EXT:pxa_newsletter_subscription/Resources/Private/Templates/**
    And email template goes to **EXT:pxa_newsletter_subscription/Resources/Private/Templates/EmailNotification/**
