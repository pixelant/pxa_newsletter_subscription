<?php

namespace Pixelant\PxaNewsletterSubscription\Tests\Unit\Domain\Validator;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\Domain\Validator\SubscriptionValidator;

/**
 * Class SubscriptionValidatorTest
 * @package Pixelant\PxaNewsletterSubscription\Tests\Unit\Domain\Validator
 */
class SubscriptionValidatorTest extends UnitTestCase
{
    /**
     * @var SubscriptionValidator
     */
    protected $subject = null;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->subject = $this->getMockBuilder(SubscriptionValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     * @dataProvider subscriptionProviderNotConfirmed
     */
    public function existsUnconfirmedTestDifferentSubscription($subscription, $expect)
    {
        $callResult = $this->callInaccessibleMethod($this->subject, 'existsUnconfirmed', $subscription);

        $this->assertEquals($expect, $callResult);
    }

    /**
     * @test
     * @dataProvider subscriptionProviderConfirmed
     */
    public function existInPidTestDifferentSubscription($subscription, $expect)
    {
        $callResult = $this->callInaccessibleMethod($this->subject, 'existInPid', $subscription);

        $this->assertEquals($expect, $callResult);
    }

    /**
     * @return array
     */
    public function subscriptionProviderConfirmed()
    {
        $subscriptionNotConfirmed = new Subscription();
        $subscriptionNotConfirmed->setHidden(true);

        $subscriptionConfirmed = clone $subscriptionNotConfirmed;
        $subscriptionConfirmed->setHidden(false);

        return [
            [null, false], // Subscription null, result false
            [$subscriptionNotConfirmed, false], // Result true
            [$subscriptionConfirmed, true], // Result false
        ];
    }

    /**
     * @return array
     */
    public function subscriptionProviderNotConfirmed()
    {
        $subscriptionNotConfirmed = new Subscription();
        $subscriptionNotConfirmed->setHidden(true);

        $subscriptionConfirmed = clone $subscriptionNotConfirmed;
        $subscriptionConfirmed->setHidden(false);

        return [
            [null, false], // Subscription null, result false
            [$subscriptionNotConfirmed, true], // Result true
            [$subscriptionConfirmed, false], // Result false
        ];
    }
}
