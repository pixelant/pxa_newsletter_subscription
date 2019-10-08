<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Domain\Validator;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\Domain\Repository\SubscriptionRepository;
use Pixelant\PxaNewsletterSubscription\Notification\Builder\UserConfirmationNotification;
use Pixelant\PxaNewsletterSubscription\Notification\Notificator;
use Pixelant\PxaNewsletterSubscription\Service\FlexFormSettingsService;
use Pixelant\PxaNewsletterSubscription\SignalSlot\EmitSignal;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Class SubscriptionValidator
 * @package Pixelant\PxaNewsletterSubscription\Domain\Validator
 */
class SubscriptionValidator extends AbstractValidator
{
    use EmitSignal;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository = null;

    /**
     * @var FlexFormSettingsService
     */
    protected $flexFormSettingsService = null;

    /**
     * @param FlexFormSettingsService $flexFormSettingsService
     */
    public function injectFlexFormSettingsService(FlexFormSettingsService $flexFormSettingsService)
    {
        $this->flexFormSettingsService = $flexFormSettingsService;
    }

    /**
     * @param SubscriptionRepository $subscriptionRepository
     */
    public function injectSubscriptionRepository(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param Subscription $subscription
     */
    protected function isValid($subscription)
    {
        $settings = array_merge($this->getPluginSettings(), $this->getPluginFlexFormSettings());

        $this->emitSignal(__CLASS__, 'beforeSubscriptionValidation' . __METHOD__, $subscription, $settings);

        $isEmailValid = GeneralUtility::validEmail($subscription->getEmail());
        $storage = (int)$settings['storagePid'];

        $existingSubscription = $isEmailValid
            ? $this->subscriptionRepository->findByEmailAndPidHidden($subscription->getEmail(), $storage)
            : null;

        if ($storage <= 0) {
            $this->addError(
                $this->translateErrorMessage('error.invalid.storage_pid', 'PxaNewsletterSubscription'),
                1566478535950
            );
        } elseif (!$isEmailValid) {
            $this->result->forProperty('email')->addError(
                new Error(
                    $this->translateErrorMessage('error.invalid.email', 'PxaNewsletterSubscription'),
                    1566476320803
                )
            );
        } elseif ($this->alreadyExistInPid($existingSubscription)) {
            $this->result->forProperty('email')->addError(
                new Error(
                    $this->translateErrorMessage('error.already_subscribed', 'PxaNewsletterSubscription'),
                    1570527355420
                )
            );
        } elseif ($this->alreadyExistButNotConfirmed($existingSubscription)) {
            if ($settings['resendConfirmationEmail']) {
                $builder = GeneralUtility::makeInstance(
                    UserConfirmationNotification::class,
                    $existingSubscription,
                    $settings
                );
                GeneralUtility::makeInstance(Notificator::class)->build($builder)->notify();
            }

            $this->result->forProperty('email')->addError(
                new Error(
                    $this->translateErrorMessage('error.waiting_for_confirmation', 'PxaNewsletterSubscription'),
                    1570527361341
                )
            );
        } elseif (!empty($settings['nameIsMandatory']) && empty($subscription->getName())) {
            $this->result->forProperty('name')->addError(
                new Error(
                    $this->translateErrorMessage('error.invalid.name', 'PxaNewsletterSubscription'),
                    1570527367070
                )
            );
        } elseif (!empty($settings['acceptTermsLink']) && $subscription->isAcceptTerms() === false) {
            $this->result->forProperty('acceptTerms')->addError(
                new Error(
                    $this->translateErrorMessage('error.invalid.accept_terms', 'PxaNewsletterSubscription'),
                    1566478593787
                )
            );
        }
    }

    /**
     * Check if given subscription exist, but not confirmed yet
     *
     * @param Subscription|null $subscription
     * @return bool
     */
    protected function alreadyExistButNotConfirmed(?Subscription $subscription): bool
    {
        return $subscription !== null && $subscription->isHidden();
    }

    /**
     * Check if subscription was confirmed
     *
     * @param Subscription|null $subscription
     * @return bool
     */
    protected function alreadyExistInPid(?Subscription $subscription): bool
    {
        return $subscription !== null && !$subscription->isHidden();
    }

    /**
     * Plugin settings
     *
     * @return array
     */
    protected function getPluginFlexFormSettings(): array
    {
        $ceUid = GeneralUtility::_GP('tx_pxanewslettersubscription_subscription')['ceUid'] ?? 0;

        return $this->flexFormSettingsService->getFlexFormArray((int)$ceUid)['settings'] ?? [];
    }

    /**
     * Get plugin settings
     *
     * @return array
     */
    protected function getPluginSettings(): array
    {
        $settings = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(ConfigurationManagerInterface::class)
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);

        return $settings;
    }
}
