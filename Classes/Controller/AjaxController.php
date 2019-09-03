<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Controller;

use Pixelant\PxaNewsletterSubscription\Controller\Traits\TranslateTrait;
use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\SignalSlot\EmitSignal;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Validation\Error;

/**
 * Class AjaxController
 * @package Pixelant\PxaNewsletterSubscription\Controller
 */
class AjaxController extends AbstractController
{
    use TranslateTrait;
    use EmitSignal;

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
        $this->mergeSettingsWithFlexFormSettings();
    }

    /**
     * Subscribe action
     *
     * @param Subscription $subscription
     * @TYPO3\CMS\Extbase\Annotation\Validate("Pixelant\PxaNewsletterSubscription\Domain\Validator\SubscriptionValidator", param="subscription")
     */
    public function subscribeAction(Subscription $subscription): void
    {
        $enableEmailConfirmation = !empty($this->settings['enableEmailConfirmation']);

        // Set properties
        $subscription->setPid((int)$this->settings['storagePid']);
        $subscription->setHidden($enableEmailConfirmation);

        // Hook before persist
        $this->emitSignal(__CLASS__, 'beforePersistSubscription' . __METHOD__, $subscription, $this->settings);

        // Save
        $this->subscriptionRepository->add($subscription);
        // Persist so we can use Uid later
        $this->objectManager->get(PersistenceManagerInterface::class)->persistAll();

        // Hook after persist
        $this->emitSignal(__CLASS__, 'afterPersistSubscription' . __METHOD__, $subscription, $this->settings);

        if ($enableEmailConfirmation) {
            // Send user confirmation email
            $this->sendSubscribeConfirmationEmail($subscription);
        } else {
            // Notify admin if no confirmation required,
            // otherwise it'll be send after confirmation
            $this->sendAdminNewSubscriptionEmail($subscription);

            // Notify subscriber
            $this->sendSubscriberSuccessSubscriptionEmail($subscription);
        }

        $response = [
            'success' => true,
            'message' => $this->translate(
                $enableEmailConfirmation
                    ? 'subscription.subscribed_confirm'
                    : 'subscription.subscribed_noconfirm'
            )
        ];

        $this->view->assign('value', $response);
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

            $validationResult = $this->arguments->getArgument('subscription')->validate();

            // Get errors
            foreach ($validationResult->getErrors() as $error) {
                if (!isset($errors[$error->getCode()])) {
                    $errors[$error->getCode()] = $error->getMessage();
                }
            }

            // Get for properties
            foreach ($validationResult->getSubResults() as $property => $result) {
                foreach ($result->getErrors() as $subError) {
                    $subErrorCode = $subError->getCode();
                    // Save only unique errors
                    if (!isset($errors[$property][$subErrorCode])) {
                        $errors[$property][$subErrorCode] = $subError->getMessage();
                    }
                }
            }

            if (count($errors) > 0) {
                $success = false;

                $this->throwStatus(400, null, json_encode(compact('success', 'errors')));
            }
        }

        return parent::errorAction();
    }
}
