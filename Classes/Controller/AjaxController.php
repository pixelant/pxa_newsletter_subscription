<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Controller;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;
use TYPO3\CMS\Extbase\Validation\Error;

/**
 * Class AjaxController
 * @package Pixelant\PxaNewsletterSubscription\Controller
 */
class AjaxController extends ActionController
{
    /**
     * @var JsonView
     */
    protected $view;

    /**
     * @var string
     */
    protected $defaultViewObjectName = JsonView::class;

    /**
     * Prepare settings
     */
    protected function initializeSubscribeAction()
    {
        if ($this->request->hasArgument('ceUid')) {
            $this->settings = array_merge(
                $this->settings,
                $this->getFlexFormSettings((int)$this->request->getArgument('ceUid'))
            );
        }
    }

    /**
     * Subscribe action
     *
     * @param Subscription $subscription
     * @TYPO3\CMS\Extbase\Annotation\Validate("Pixelant\PxaNewsletterSubscription\Domain\Validator\SubscriptionValidator", param="subscription")
     */
    public function subscribeAction(Subscription $subscription): void
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($subscription, 'Debug', 16);
        die;
        $this->view->assign('value', ['success' => true]);
    }

    /**
     * Handle subscription errors
     *
     * @return string|void
     */
    public function errorAction()
    {
        if ($this->arguments->hasArgument('subscription')) {
            /** @var Error[] $errors */
            $errors = [];

            foreach ($this->arguments->getArgument('subscription')->validate()->getErrors() as $error) {
                if (!isset($errors[$error->getCode()])) {
                    $errors[$error->getCode()] = $error->getMessage();
                }
            }

            $success = false;

            $this->throwStatus(401, null, json_encode(compact('success', 'errors')));
        }

        return parent::errorAction();
    }

    /**
     * Read content element flexform settings
     *
     * @param int $ceUid
     * @return array
     */
    protected function getFlexFormSettings(int $ceUid): array
    {
        $flexFormString = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content')
            ->select(
                ['pi_flexform'],
                'tt_content',
                ['uid' => $ceUid]
            )
            ->fetchColumn(0);

        if (!empty($flexFormString)) {
            $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
            return $flexFormService->convertFlexFormContentToArray($flexFormString)['settings'] ?? [];
        }

        return [];
    }
}
