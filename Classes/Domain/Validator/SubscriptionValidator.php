<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Domain\Validator;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\Domain\Repository\SubscriptionRepository;
use Pixelant\PxaNewsletterSubscription\Service\FlexFormSettingsService;
use Pixelant\PxaNewsletterSubscription\SignalSlot\EmitSignal;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        $settings = $this->getPluginFlexFormSettings();
        $isNameRequired = boolval($settings['nameIsMandatory'] ?? false);

        $this->emitSignal('beforeSubscriptionValidation' . __METHOD__, $subscription, $settings);

        if (empty($settings['storagePid'])) {
            $this->addError(
                $this->translateErrorMessage('error.invalid.storagePid', 'PxaNewsletterSubscription'),
                1566478535950
            );
        } elseif (!GeneralUtility::validEmail($subscription->getEmail())) {
            $this->result->forProperty('email')->addError(
                new Error(
                    $this->translateErrorMessage('error.invalid.email', 'PxaNewsletterSubscription'),
                    1566476320803
                )
            );
        } elseif ($this->alreadyExistInPid($subscription->getEmail(), (int)$settings['storagePid'])) {
            $this->result->forProperty('email')->addError(
                new Error(
                    $this->translateErrorMessage('error.subscribe.already-subscribed', 'PxaNewsletterSubscription'),
                    1566478593787
                )
            );
        } elseif ($isNameRequired && empty($subscription->getName())) {
            $this->result->forProperty('name')->addError(
                new Error(
                    $this->translateErrorMessage('error.invalid.name', 'PxaNewsletterSubscription'),
                    1566478593787
                )
            );
        }
    }

    /**
     * Check if subscription for such email exist
     *
     * @param string $email
     * @param int $pid
     * @return bool
     */
    protected function alreadyExistInPid(string $email, int $pid): bool
    {
        return $this->subscriptionRepository->findByEmailAndPid($email, $pid) !== null;
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
}
