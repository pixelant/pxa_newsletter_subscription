<?php

namespace Pixelant\PxaNewsletterSubscription\Service;

use Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUser;
use Pixelant\PxaNewsletterSubscription\Domain\Model\Address;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Send notification emails
 *
 * Class EmailNotificationService
 * @package Pixelant\PxaNewsletterSubscription\Service
 */
class EmailNotificationService
{
    const LINK_HOLDER = '{link}';

    /**
     * @var MailMessage
     */
    protected $mailMessage;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Initialize main vars
     *
     * @param array $settings
     */
    public function __construct($settings)
    {
        $this->mailMessage = GeneralUtility::makeInstance(MailMessage::class);

        // plugin settings
        $this->settings = $settings;
    }

    /**
     * Send email
     *
     * @param FrontendUser|Address $subscriber
     * @param string $link
     * @param bool $unSubscribeMail
     * @return void
     */
    public function sendConfirmationEmail($subscriber, $link, $unSubscribeMail)
    {
        $link = $this->generateLink($link);

        $this->mailMessage->setFrom($this->getSender());
        $this->mailMessage->setTo($subscriber->getEmail());
        $this->mailMessage->setSubject($this->getConfirmMailSubject());
        $this->mailMessage->setBody(
            $this->getConfirmMailBody($subscriber, $link, $unSubscribeMail),
            'text/html'
        );

        $this->mailMessage->send();
    }

    /**
     * Get sender name and email
     *
     * @return array
     */
    protected function getSender()
    {
        // Override with flexform settings if set and valid
        if (!empty($this->settings['confirmMailSenderName'])) {
            $confirmMailSenderName = $this->settings['confirmMailSenderName'];
        } else {
            $confirmMailSenderName = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'];
        }

        if (GeneralUtility::validEmail($this->settings['confirmMailSenderEmail'])) {
            $confirmMailSenderEmail = $this->settings['confirmMailSenderEmail'];
        } else {
            $confirmMailSenderEmail = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];
        }

        // If from email is still empty, use a no-reply address
        if (empty($confirmMailSenderEmail)) {
            // Won't work on all domains!
            $domain = GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');
            if (substr($domain, 0, 3) == 'www') {
                $domain = substr($domain, 4);
            }

            $confirmMailSenderEmail = 'no-reply@' . $domain;
        }

        if (empty($confirmMailSenderName)) {
            $confirmMailSenderName = $confirmMailSenderEmail;
        }

        return [$confirmMailSenderEmail => $confirmMailSenderName];
    }

    /**
     * Generates the Body string for confirmation mail
     *
     * @param FrontendUser|Address $subscriber
     * @param $link
     * @param $unSubscribeMail
     * @return string
     */
    protected function getConfirmMailBody($subscriber, $link, $unSubscribeMail)
    {
        // Check flex form value
        $bodyText = $unSubscribeMail ?
            $this->settings['confirmMailUnsubscribeBody'] : $this->settings['confirmMailSubscribeBody'];

        if (empty($bodyText)) {
            // Set defaults from original translation, has replacement in texts
            $bodyText = LocalizationUtility::translate(
                'confirm_mail_greeting',
                'pxa_newsletter_subscription',
                [$subscriber->getName()]
            );
        }

        if (strpos($bodyText, self::LINK_HOLDER) !== false) {
            $bodyText = str_replace(self::LINK_HOLDER, $link, $bodyText);
        } else {
            // append
            $bodyText .= sprintf(
                '<p>%s</p>',
                $link
            );
        }

        return $bodyText;
    }

    /**
     * If link text is set, generate HTML for link
     *
     * @param string $link
     * @return string
     */
    protected function generateLink($link)
    {
        if (!empty($this->settings['confirmLinkText'])) {
            $link = sprintf(
                '<a href="%s">%s</a>',
                $link,
                $this->settings['confirmLinkText']
            );
        }

        return $link;
    }

    /**
     * Generates the Subject for confirmation mail
     *
     * @return string
     */
    protected function getConfirmMailSubject()
    {
        return empty($this->settings['confirmMailSubject']) ?
            LocalizationUtility::translate('confirm_mail_subject', 'pxa_newsletter_subscription') :
            $this->settings['confirmMailSubject'];
    }
}
