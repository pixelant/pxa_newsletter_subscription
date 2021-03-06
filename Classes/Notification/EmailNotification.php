<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Email notification service
 *
 * @package Pixelant\PxaNewsletterSubscription\Service\Notification
 */
class EmailNotification implements NotificationInterface
{
    /**
     * @var MailMessage
     */
    protected $mailMessage = null;

    /**
     * Notification template name in EXT:pxa_newsletter_subscription/Resources/Private/Templates/Notification/
     *
     * @return string
     */
    protected $notificationTemplateName = null;

    /**
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * @var StandaloneView
     */
    protected $view = null;

    /**
     * @var string
     */
    protected $notificationControllerName = 'EmailNotification';

    /**
     * @var string
     */
    protected $senderName = '';

    /**
     * @var string
     */
    protected $senderEmail = '';

    /**
     * @var array
     */
    protected $recipients = [];

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * Initialize constructor
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->mailMessage = GeneralUtility::makeInstance(MailMessage::class);
    }

    /**
     * Notify
     *
     * @param bool $useHtmlFormat
     * @return bool
     */
    public function notify(bool $useHtmlFormat = true): bool
    {
        $this->prepareMessage();
        $notificationMessage = $this->getNotificationMessage();

        // TYPO3 9
        if ($this->isTypo3VersionLowerThan10()) {
            $this->mailMessage->setBody($notificationMessage, $useHtmlFormat ? 'text/html' : null);
        } else {
            // The API changed In TYPO3 v10
            if ($useHtmlFormat) {
                $this->mailMessage->html($notificationMessage);
            } else {
                $this->mailMessage->text($notificationMessage);
            }
        }

        return (bool)$this->mailMessage->send();
    }

    /**
     * Add variables to mail template
     *
     * @param array $variables
     */
    public function assignVariables(array $variables): void
    {
        $this->getView()->assignMultiple($variables);
    }

    /**
     * @param string $senderName
     * @return EmailNotification
     */
    public function setSenderName(string $senderName): EmailNotification
    {
        $this->senderName = $senderName;
        return $this;
    }

    /**
     * @param string $senderEmail
     * @return EmailNotification
     */
    public function setSenderEmail(string $senderEmail): EmailNotification
    {
        $this->senderEmail = $senderEmail;
        return $this;
    }

    /**
     * @param array $recipients
     * @return EmailNotification
     */
    public function setRecipients(array $recipients): EmailNotification
    {
        $this->recipients = $recipients;
        return $this;
    }

    /**
     * @param string $subject
     * @return EmailNotification
     */
    public function setSubject(string $subject): EmailNotification
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotificationTemplateName()
    {
        return $this->notificationTemplateName;
    }

    /**
     * @param mixed $notificationTemplateName
     * @return EmailNotification
     */
    public function setNotificationTemplateName($notificationTemplateName): EmailNotification
    {
        $this->notificationTemplateName = $notificationTemplateName;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotificationControllerName(): string
    {
        return $this->notificationControllerName;
    }

    /**
     * @param string $notificationControllerName
     * @return EmailNotification
     */
    public function setNotificationControllerName(string $notificationControllerName): EmailNotification
    {
        $this->notificationControllerName = $notificationControllerName;
        return $this;
    }

    /**
     * Prepare email message for sending
     */
    protected function prepareMessage(): void
    {
        $this->validateRecipients();

        if (empty($this->subject)) {
            throw new \InvalidArgumentException(
                '"subject" is required in order to send notification email',
                1566546703887
            );
        }
        if (empty($this->recipients)) {
            throw new \InvalidArgumentException('"recipients" require at least one valid email.', 1566546708838);
        }

        $this->mailMessage
            ->setFrom($this->getSenderNameAndEmail())
            ->setTo($this->recipients)
            ->setSubject($this->subject);
    }

    /**
     * Generate sender email and name
     *
     * @return array
     */
    protected function getSenderNameAndEmail(): array
    {
        if (empty($this->senderEmail)) {
            $this->senderEmail = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] ?? '';
        }

        if (!GeneralUtility::validEmail($this->senderEmail)) {
            throw new \InvalidArgumentException('"senderEmail" must be a valid email.', 1566546713846);
        }

        if (empty($this->senderName)) {
            $this->senderName = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?? '';
        }

        if (empty($this->senderName)) {
            return [$this->senderEmail];
        }

        return [$this->senderEmail => $this->senderName];
    }

    /**
     * Render notification message
     *
     * @return string
     */
    protected function getNotificationMessage(): string
    {
        return $this->getView()->render();
    }

    /**
     * Validate recipients of email
     */
    protected function validateRecipients(): void
    {
        $this->recipients = array_filter(
            $this->recipients,
            function ($recipient) {
                return GeneralUtility::validEmail($recipient);
            }
        );
    }

    /**
     * Return notification view
     *
     * @return StandaloneView
     */
    protected function getView(): StandaloneView
    {
        if ($this->view === null) {
            $this->initializeStandaloneView();
        }

        return $this->view;
    }

    /**
     * Check if TYPO3 version is below 10
     *
     * @return bool
     */
    protected function isTypo3VersionLowerThan10(): bool
    {
        return VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) < 10000000;
    }

    /**
     * Initialize view
     *
     * @return void
     */
    protected function initializeStandaloneView(): void
    {
        if (empty($this->notificationTemplateName)) {
            throw new \UnexpectedValueException('"notificationTemplateName" could not be empty value', 1567144351831);
        }

        $extbaseSettings = $this->objectManager
            ->get(ConfigurationManagerInterface::class)
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        /** @var StandaloneView $standaloneView */
        $standaloneView = $this->objectManager->get(StandaloneView::class);

        $view = $extbaseSettings['view'];
        if (isset($view['templateRootPaths']) && is_array($view['templateRootPaths'])) {
            $standaloneView->setTemplateRootPaths($view['templateRootPaths']);
        }

        if (isset($view['partialRootPaths']) && is_array($view['partialRootPaths'])) {
            $standaloneView->setPartialRootPaths($view['partialRootPaths']);
        }

        if (isset($view['layoutRootPaths']) && is_array($view['layoutRootPaths'])) {
            $standaloneView->setLayoutRootPaths($view['layoutRootPaths']);
        }

        $standaloneView->getRenderingContext()->setControllerName($this->notificationControllerName);
        $standaloneView->getRenderingContext()->setControllerAction($this->notificationTemplateName);

        $standaloneView->assign('settings', $extbaseSettings['settings']);

        $this->view = $standaloneView;
    }
}
