<?php

namespace Pixelant\PxaNewsletterSubscription\Service;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Send emails
 *
 * Class EmailService
 * @package Pixelant\PxaNewsletterSubscription\Service
 */
class MailService
{
    /**
     * CONTENT_TYPE_HTML
     */
    const CONTENT_TYPE_HTML = 'text/html';

    /**
     * @var MailMessage
     */
    protected $mailMessage;

    /**
     * @var array $sender
     */
    protected $sender;

    /**
     * @var string $subject
     */
    protected $subject;

    /**
     * @var string $body
     */
    protected $body;

    /**
     * @var string $contentType
     */
    protected $contentType;

    /**
     * Initialize main vars
     * @param string $contentType
     */
    public function __construct($contentType = self::CONTENT_TYPE_HTML)
    {
        $this->mailMessage = GeneralUtility::makeInstance(MailMessage::class);
        $this->contentType = $contentType;
    }

    /**
     * Send email
     * @return void
     */
    public function send()
    {
        $this->mailMessage->send();
    }

    /**
     * @param $email
     * @param $name
     */
    public function setSender($email, $name)
    {
        // Override with sender if set and valid
        $name = $name ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'];

        $email = GeneralUtility::validEmail($email)
            ? $email : $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];

        // If from email is still empty, use a no-reply address
        if (empty($email)) {
            // Won't work on all domains!
            $domain = GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');
            if (substr($domain, 0, 3) == 'www') {
                $domain = substr($domain, 4);
            }

            $email = 'no-reply@' . $domain;
        }

        if (empty($name)) {
            $name = $email;
        }

        $this->mailMessage->setFrom([$email => $name]);
    }

    /**
     * @param $email
     * @param $name
     */
    public function setReceiver($email, $name = '')
    {
        $this->mailMessage->setTo([$email => $name]);
    }

    /**
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->mailMessage->setSubject($subject);
    }

    /**
     * @param $body
     */
    public function setBody($body)
    {
        $this->mailMessage->setBody($body, $this->contentType);
    }
}
