<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class AbstractEmailNotification
 * @package Pixelant\PxaNewsletterSubscription\Service\Notification
 */
class EmailNotification implements NotificationInterface
{
    const FORMAT_HTML = 'text/html';

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
    protected $receivers = [];

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
     * @return bool
     */
    public function notify(): bool
    {
        return $this->send();
    }

    /**
     * Send email
     *
     * @param string $format
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function send(string $format = self::FORMAT_HTML): bool
    {
        $this->validateReceivers();

        if (empty($this->subject)) {
            throw new \InvalidArgumentException('"subject" is required in order to send notification email', 1566546703887);
        }
        if (empty($this->receivers)) {
            throw new \InvalidArgumentException('"receivers" require at least one valid email.', 1566546708838);
        }

        $this->mailMessage
            ->setFrom($this->getSenderNameAndEmail())
            ->setTo($this->receivers)
            ->setSubject($this->subject)
            ->setBody($this->getNotificationMessage(), $format);

        return $this->mailMessage->send() > 0;
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
     * @param array $receivers
     * @return EmailNotification
     */
    public function setReceivers(array $receivers): EmailNotification
    {
        $this->receivers = $receivers;
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
     * Validate receivers of email
     */
    protected function validateReceivers(): void
    {
        $this->receivers = array_filter(
            $this->receivers,
            function ($receiver) {
                return GeneralUtility::validEmail($receiver);
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
