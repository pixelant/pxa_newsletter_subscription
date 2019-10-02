<?php

namespace Pixelant\PxaNewsletterSubscription\Tests\Unit\Controller;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pixelant\PxaNewsletterSubscription\Controller\NewsletterSubscriptionController;
use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;

/**
 * Class NewsletterSubscriptionControllerTest
 * @package Pixelant\PxaNewsletterSubscription\Tests\Unit\Controller
 */
class NewsletterSubscriptionControllerTest extends UnitTestCase
{
    /**
     * @var NewsletterSubscriptionController
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->subject = $this->getMockBuilder(NewsletterSubscriptionController::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFlashMessage', 'sendNotification', 'translate'])
            ->getMock();
    }

    /**
     * @test
     */
    public function checkPageTypeSettingsShowErrorIfNotPageTypeSet()
    {
        $settings = [];

        $this->inject($this->subject, 'settings', $settings);

        $this->subject
            ->expects($this->once())
            ->method('addFlashMessage');

        $this->callInaccessibleMethod($this->subject, 'checkPageTypeSettings');
    }

    /**
     * @test
     * @dataProvider emailsInvalidConfigurations
     */
    public function checkSenderEmailShowErrorIfEmailsEnabledButNoSenderEmail($settings)
    {
        $this->inject($this->subject, 'settings', $settings);
        $this->subject
            ->expects($this->once())
            ->method('addFlashMessage');

        $this->callInaccessibleMethod($this->subject, 'checkSenderEmail');
    }

    /**
     * @test
     * @dataProvider emailsValidConfigurations
     */
    public function checkSenderEmailWillNotShowErrorIfEmailsEnabledAndSenderEmailValid($settings)
    {
        $this->inject($this->subject, 'settings', $settings);
        $this->subject
            ->expects($this->never())
            ->method('addFlashMessage');

        $this->callInaccessibleMethod($this->subject, 'checkSenderEmail');
    }

    /**
     * @test
     */
    public function sendSubscriberSuccessSubscriptionEmailWillNotSendEmailIfDisabled()
    {
        $settings = ['notifySubscriber' => false];
        $this->inject($this->subject, 'settings', $settings);

        $this->subject
            ->expects($this->never())
            ->method('sendNotification');

        $this->callInaccessibleMethod($this->subject, 'sendSubscriberSuccessSubscriptionEmail', new Subscription());
    }

    /**
     * @test
     */
    public function sendAdminNewSubscriptionEmailWillNotSendEmailIfNotAdminEmail()
    {
        $settings = ['notifyAdmin' => ''];
        $this->inject($this->subject, 'settings', $settings);

        $this->subject
            ->expects($this->never())
            ->method('sendNotification');

        $this->callInaccessibleMethod($this->subject, 'sendAdminNewSubscriptionEmail', new Subscription());
    }

    /**
     * @return array
     */
    public function emailsValidConfigurations()
    {
        return [
            [
                ['notifyAdmin' => 'admin@mail.com', 'senderEmail' => 'test@site.com']
            ],
            [
                ['notifySubscriber' => true, 'senderEmail' => 'test@site.com']
            ],
            [
                ['enableEmailConfirmation' => true, 'senderEmail' => 'test@site.com']
            ],
        ];
    }

    /**
     * @return array
     */
    public function emailsInvalidConfigurations()
    {
        return [
            [
                ['notifyAdmin' => 'admin@mail.com', 'senderEmail' => '']
            ],
            [
                ['notifySubscriber' => true, 'senderEmail' => '']
            ],
            [
                ['enableEmailConfirmation' => true, 'senderEmail' => '']
            ],
        ];
    }
}
