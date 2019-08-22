<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Domain\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Class SubscriptionValidator
 * @package Pixelant\PxaNewsletterSubscription\Domain\Validator
 */
class SubscriptionValidator extends AbstractValidator
{

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param mixed $value
     */
    protected function isValid($value)
    {
        /*$this->addError('Test', 1566460998433);*/

    }
}
