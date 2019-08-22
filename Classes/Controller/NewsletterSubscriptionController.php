<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Controller;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class NewsletterSubscriptionController
 * @package Pixelant\PxaNewsletterSubscription\Controller
 */
class NewsletterSubscriptionController extends ActionController
{
    /**
     * Show form
     */
    public function formAction()
    {
        $this->checkPageTypeSettings();

        $this->view->assign('ceUid', $this->configurationManager->getContentObject()->getFieldVal('uid'));
    }

    /**
     * Check if ajax page type is set in settings
     * Add flash message if setting is missing
     */
    protected function checkPageTypeSettings(): void
    {
        if (empty($this->settings['ajaxPageType'])) {
            $this->addFlashMessage(
                LocalizationUtility::translate('error.missing_page_type', $this->extensionName),
                '',
                FlashMessage::ERROR
            );
        }
    }
}
