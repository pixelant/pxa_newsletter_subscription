<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Service\Notification;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class AbstractEmailNotification
 * @package Pixelant\PxaNewsletterSubscription\Service\Notification
 */
abstract class AbstractEmailNotification
{
    /**
     * Email formats
     */
    const FORMAT_PLAINTEXT = 'text/plain';
    const FORMAT_HTML = 'text/html';

    /**
     * @var MailMessage
     */
    protected $mailMessage = null;

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
    protected $notificationSimulatedControllerName = 'Notification';

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
     * @return AbstractEmailNotification
     */
    public function setSenderName(string $senderName): AbstractEmailNotification
    {
        $this->senderName = $senderName;
        return $this;
    }

    /**
     * @param string $senderEmail
     * @return AbstractEmailNotification
     */
    public function setSenderEmail(string $senderEmail): AbstractEmailNotification
    {
        $this->senderEmail = $senderEmail;
        return $this;
    }

    /**
     * @param array $receivers
     * @return AbstractEmailNotification
     */
    public function setReceivers(array $receivers): AbstractEmailNotification
    {
        $this->receivers = $receivers;
        return $this;
    }

    /**
     * @param string $subject
     * @return AbstractEmailNotification
     */
    public function setSubject(string $subject): AbstractEmailNotification
    {
        $this->subject = $subject;
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
     * @param string $templatePathAndFilename
     * @param string $templateName
     * @return StandaloneView
     */
    protected function initializeStandaloneView(
        string $templatePathAndFilename = '',
        string $templateName = ''
    ): void {
        $extbaseSettings = $this->objectManager->get(ConfigurationManagerInterface::class)
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        /** @var StandaloneView $standaloneView */
        $standaloneView = $this->objectManager->get(StandaloneView::class);

        if (!empty($templatePathAndFilename)) {
            $standaloneView->setTemplatePathAndFilename($templatePathAndFilename);
        } else {
            $standaloneView->setTemplate($templateName);
        }

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

        $standaloneView->getRenderingContext()->setControllerName($this->notificationSimulatedControllerName);
        $standaloneView->getRenderingContext()->setControllerAction($this->getNotificationTemplateName());

        $standaloneView->assign('settings', $extbaseSettings['settings']);

        $this->view = $standaloneView;
    }

    /**
     * Notification template name in EXT:pxa_newsletter_subscription/Resources/Private/Templates/Notification/
     *
     * @return string
     */
    abstract public function getNotificationTemplateName(): string;
}
