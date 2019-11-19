.. include:: ../../Includes.txt


.. _email-templates:

Email notifications templates
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

It is possible to use custom email templates. They work in the same way

Set new template paths

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


After setting the template path, you can create new templates.

List of Email notification templates:

- AdminNewSubscription.html - Admin email after user subscription is finished
- AdminUnsubscribe.html - Admin email after user unsubscribed
- SubscribeConfirmation.html - Email to subscriber with confirmation link
- UserSuccessSubscription.html - Email to subscriber with notification about successful subscription
- UserUnsubscribeConfirmation.html - Email to subscriber with unsubscribe confirmation link

.. tip::

    According to TypoScript example you should put custom templates under folder:
    **EXT:my_custom_extension/Resources/Private/Templates/**
    And email template goes to **EXT:my_custom_extension/Resources/Private/Templates/EmailNotification/**
