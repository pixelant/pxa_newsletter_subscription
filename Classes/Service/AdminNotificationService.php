<?php

namespace Pixelant\PxaNewsletterSubscription\Service;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Address;
use Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUser;
use Pixelant\PxaNewsletterSubscription\Utility\AbstractStorageUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Send admin notification emails
 *
 * Class AdminNotificationService
 * @package Pixelant\PxaNewsletterSubscription\Service
 */
class AdminNotificationService
{
    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var string $action
     */
    protected $action;

    /**
     * @var array $settings
     */
    protected $settings;

    /**
     * Initialize main vars
     *
     * @param array $settings
     * @param $action
     */
    public function __construct($settings, $action = 'subscribe')
    {
        $this->mailService = GeneralUtility::makeInstance(MailService::class);

        // Set settings
        $this->settings = $settings;
        $this->action = $action;
    }

    /**
     * Send email
     *
     * @param AbstractStorageUtility $storageUtility
     * @param FrontendUser|Address|null $subscriber
     * @param string $senderEmail
     * @param string $senderName
     * @param string $receiverEmail
     * @param string $receiverName
     * @param string $subject
     * @param string $body
     * @return void
     * @internal param FrontendUser $feUser
     * @internal param string $link
     * @internal param bool $unSubscribeMail
     */
    public function sendNotification(
        $storageUtility,
        $subscriber,
        $senderEmail = '',
        $senderName = '',
        $receiverEmail = '',
        $receiverName = '',
        $subject = '',
        $body = ''
    ) {
        // Sender
        $senderEmail = $senderEmail ?: $this->settings['senderEmail'];
        $senderName = $senderName ?: $this->settings['senderName'];

        // Receiver
        $receiverEmail = $receiverEmail ?: $this->settings['receiverEmail'];
        $receiverName = $receiverName ?: $this->settings['receiverName'];

        // Subject
        $subject = $subject ?: $this->settings['subject'];
        $subject = $subject ?: LocalizationUtility::translate(
            'admin_notification_subject',
            'pxa_newsletter_subscription'
        );

        // Body
        if (!$body) {
            if ($this->isSubscribeAction()) {
                $body = $this->settings['subscribeBody'];
            }

            if ($this->isUnsubscribeAction()) {
                $body = $this->settings['unsubscribeBody'];
            }
        }

        $body .= $storageUtility->getEmailBody($subscriber, $subscriber);

        // Setup
        $this->mailService->setSender($senderEmail, $senderName);
        $this->mailService->setReceiver($receiverEmail, $receiverName);
        $this->mailService->setSubject($subject);
        $this->mailService->setBody($body);

        // Send
        $this->mailService->send();
    }

    /**
     * @param $frontendUser
     * @return string
     */
    protected function getSubscriptionInfo($frontendUser)
    {
        $result = '<div>';
        $result .= '<p>' . 'Username: ' . $frontendUser->getUsername() . '</p>';
        $result .= '<p>' . 'Name: ' . $frontendUser->getName() . '</p>';
        $result .= '</div>';
        return $result;
    }

    /**
     * isSubscribeAction
     * @return bool
     */
    protected function isSubscribeAction()
    {
        return $this->action == 'subscribe';
    }

    /**
     * isUnsubscribeAction
     * @return bool
     */
    protected function isUnsubscribeAction()
    {
        return $this->action == 'unsubscribe';
    }
}
