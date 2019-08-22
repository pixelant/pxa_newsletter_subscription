<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Controller;

use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * Class NewsletterSubscriptionController
 * @package Pixelant\PxaNewsletterSubscription\Controller
 */
class NewsletterSubscriptionController extends AbstractController
{
    use TranslateTrait;

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
                $this->translate('error.missing_page_type'),
                '',
                FlashMessage::ERROR
            );
        }
    }
}
