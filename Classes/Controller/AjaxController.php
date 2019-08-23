<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Controller;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\Service\Notification\SubscriberNotification;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        // Save
        $this->subscriptionRepository->add($subscription);
        // Persist so we can use Uid later
        $this->objectManager->get(PersistenceManagerInterface::class)->persistAll();


        if ($enableEmailConfirmation) {
            // Send user confirmation email
            $this->sendSubscriberNotification($subscription);
        } else {
            // Notify admin if no confirmation required,
            // otherwise it'll be send after confirmation
            $this->notifyAdmin($subscription);
        }

        $response = [
            'success' => true,
            'message' => $this->translate(
                $enableEmailConfirmation
                    ? 'success.subscribe.subscribed-confirm'
                    : 'success.subscribe.subscribed-noconfirm'
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

            $success = false;

            $this->throwStatus(401, null, json_encode(compact('success', 'errors')));
        }

        return parent::errorAction();
    }

    /**
     * Send confirmation email
     *
     * @param Subscription $subscription
     */
    protected function sendSubscriberNotification(Subscription $subscription): void
    {
        $subscriberNotification = $this->getSubscriberNotification();

        $subscriberNotification
            ->setSubject($this->translate('confirm_mail_subject'))
            ->setSenderEmail($this->settings['senderEmail'] ?? '')
            ->setSenderName($this->settings['senderName'] ?? '')
            ->setReceivers([$subscription->getEmail()]);

        $confirmationLink = $this->generateConfirmationLink(
            $subscription,
            intval($this->settings['confirmationPage']) ?: null
        );

        $subscriberNotification->assignVariables(compact('subscription', 'confirmationLink'));

        $subscriberNotification->send();
    }

    /**
     * @return SubscriberNotification
     */
    protected function getSubscriberNotification(): SubscriberNotification
    {
        return GeneralUtility::makeInstance(SubscriberNotification::class);
    }
}
