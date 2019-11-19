<?php

namespace Pixelant\PxaNewsletterSubscription\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;

/**
 * Class SubscriptionTest
 * @package Pixelant\PxaNewsletterSubscription\Tests\Unit\Domain\Model
 */
class SubscriptionTest extends UnitTestCase
{
    /**
     * @var Subscription
     */
    protected $subject = null;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->subject = new Subscription();
    }

    /**
     * @test
     */
    public function defaultValueOfHiddenFalse()
    {
        $this->assertFalse($this->subject->isHidden());
    }

    /**
     * @test
     */
    public function hiddenCanBeSet()
    {
        $this->subject->setHidden(true);

        $this->assertTrue($this->subject->isHidden());
    }

    /**
     * @test
     */
    public function defaultValueOfNameEmpty()
    {
        $this->assertEmpty($this->subject->getName());
    }

    /**
     * @test
     */
    public function nameCanBeSet()
    {
        $name = 'name';

        $this->subject->setName($name);

        $this->assertEquals($name, $this->subject->getName());
    }

    /**
     * @test
     */
    public function defaulValueOfEmailIsEmpty()
    {
        $this->assertEmpty($this->subject->getEmail());
    }

    /**
     * @test
     */
    public function emailCanBeSet()
    {
        $email = 'site@site.com';

        $this->subject->setEmail($email);

        $this->assertEquals($email, $this->subject->getEmail());
    }

    /**
     * @test
     */
    public function defaultValueOfAcceptTermsIsFalse()
    {
        $this->assertFalse($this->subject->isAcceptTerms());
    }

    /**
     * @test
     */
    public function acceptTermsCanBeSet()
    {
        $this->subject->setAcceptTerms(true);
        $this->assertTrue($this->subject->isAcceptTerms());
    }
}
