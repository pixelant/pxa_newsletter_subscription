.. include:: ../Includes.txt


.. _configuration:

=============
Configuration
=============

Target group: **Developers, Integrators**


TypoScript settings
^^^^^^^^^^^^^^^^^^^

Sender name and email
"""""""""""""""""""""

Sender name and email must be configured in order to send email notifications

.. figure:: ../Images/TSEditor.png
   :class: with-shadow
   :alt: TSEditor

   Constant editor


.. code-block:: typoscript

    # TypoScript constants
    plugin.tx_pxanewslettersubscription.settings.senderEmail =
    plugin.tx_pxanewslettersubscription.settings.senderName =


Subscription target table
"""""""""""""""""""""""""

It is possible to change the table where subscriptions are saved. Right now it's saved as frontend users in "fe_users" table.

.. tip::

    Example for TYPO3 v9

.. code-block:: typoscript

    plugin.tx_pxanewslettersubscription {
        persistence {
            classes {
                # Change mapping table
                Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription {
                    mapping {
                        tableName = another_table_name
                    }
                }
            }
        }
    }

.. tip::

    Example for TYPO3 v10

Create the PHP file`Configuration/Extbase/Persistence/Classes.php` in your extension

.. code-block:: php

    return [
        Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription::class => [
            'tableName' => 'another_table_name',
            'properties' => [
                // Properties settings
            ],
        ],
    ];

