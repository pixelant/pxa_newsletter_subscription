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

It's required to set sender name and email in order to send email notifications

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

It's possible to change table where subscriptions are saved. Right now it's saved as frontend users in "fe_users" table.

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

