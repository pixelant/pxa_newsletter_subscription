<?php

namespace Pixelant\PxaNewsletterSubscription\Tests\Unit\Notification;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pixelant\PxaNewsletterSubscription\Notification\EmailNotification;
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * Class EmailNotificationTest
 * @package Pixelant\PxaNewsletterSubscription\Tests\Unit\Notification
 */
class EmailNotificationTest extends UnitTestCase
{
    /**
     * @var EmailNotification
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            EmailNotification::class,
            ['isBelow10', 'getNotificationMessage'],
            [],
            '',
            false
        );

        $mockedMail = $this->createPartialMock(MailMessage::class, ['setBody', 'html', 'text', 'send']);

        $this->inject($this->subject, 'mailMessage', $mockedMail);
    }

    /**
     * @test
     */
    public function setSenderNameWillSetProperty()
    {
        $value = 'Sender';

        $this->subject->setSenderName($value);

        $this->assertEquals($value, $this->subject->_get('senderName'));
    }

    /**
     * @test
     */
    public function setSenderEmailWillSetProperty()
    {
        $value = 'test@mail.com';

        $this->subject->setSenderEmail($value);

        $this->assertEquals($value, $this->subject->_get('senderEmail'));
    }

    /**
     * @test
     */
    public function setSubjectWillSetProperty()
    {
        $value = 'Subject';

        $this->subject->setSubject($value);

        $this->assertEquals($value, $this->subject->_get('subject'));
    }

    /**
     * @test
     */
    public function setReceiversWillSetProperty()
    {
        $value = ['test@site.com'];

        $this->subject->setRecipients($value);

        $this->assertEquals($value, $this->subject->_get('receivers'));
    }

    /**
     * @test
     */
    public function canSetNotificationControllerName()
    {
        $value = 'TestController';

        $this->subject->setNotificationControllerName($value);

        $this->assertEquals($value, $this->subject->getNotificationControllerName());
    }

    /**
     * @test
     */
    public function canSetNotificationTemplateName()
    {
        $value = 'TestTemplate';

        $this->subject->setNotificationTemplateName($value);

        $this->assertEquals($value, $this->subject->getNotificationTemplateName());
    }

    /**
     * @test
     */
    public function validateREceiversWillRemoveAllInvalidReceivers()
    {
        $receivers = ['test@mail.com', 'invalid', 'another@mail.com'];
        $expect = [0 => 'test@mail.com', 2 => 'another@mail.com'];

        $this->subject->setRecipients($receivers);
        $this->subject->_call('validateReceivers');

        $this->assertEquals($expect, $this->subject->_get('receivers'));
    }

    /**
     * @test
     */
    public function getSenderNameAndEmailReturnArrayWithEmailAndName()
    {
        $email = 'test@maiol.com';
        $name = 'tester';

        $this->subject->setSenderEmail($email);
        $this->subject->setSenderName($name);

        $expect = [$email => $name];

        $this->assertEquals($expect, $this->subject->_call('getSenderNameAndEmail'));
    }

    /**
     * @test
     */
    public function getSenderNameAndEmailReturnArrayWithDefaultValuesIfGivenEmpty()
    {
        $email = '';
        $name = '';

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'default@mail.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'defaultname';

        $this->subject->setSenderEmail($email);
        $this->subject->setSenderName($name);

        $expect = [$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] => $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']];

        $this->assertEquals($expect, $this->subject->_call('getSenderNameAndEmail'));
    }

    /**
     * @test
     */
    public function getSenderNameAndEmailReturnArrayOnlyEmailIfNameEmpty()
    {
        $email = 'test@mail.com';
        $name = '';

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->subject->setSenderEmail($email);
        $this->subject->setSenderName($name);

        $expect = [$email];

        $this->assertEquals($expect, $this->subject->_call('getSenderNameAndEmail'));
    }

    /**
     * @test
     */
    public function getSenderNameAndEmailThrownExceptionIfSenderInvalid()
    {
        $email = 'testinvalid';
        $name = '';


        $this->subject->setSenderEmail($email);
        $this->subject->setSenderName($name);

        $this->expectException(\InvalidArgumentException::class);
        $this->subject->_call('getSenderNameAndEmail');
    }

    /**
     * @test
     */
    public function prepareMessageThrownExceptionIfSubjectEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1566546703887);

        $this->subject->setSubject('');

        $this->subject->_call('prepareMessage');
    }

    /**
     * @test
     */
    public function prepareMessageThrownExceptionIfReceiversEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1566546708838);

        $this->subject->setSubject('Test');
        $this->subject->setRecipients([]);

        $this->subject->_call('prepareMessage');
    }

    /**
     * @test
     */
    public function notifyShouldPrepareMessage()
    {
        $subject = $this->createPartialMock(EmailNotification::class, ['prepareMessage', 'isBelow10', 'getNotificationMessage']);
        $this->inject($subject, 'mailMessage', $this->createPartialMock(MailMessage::class, ['html', 'send']));

        $subject
            ->expects($this->once())
            ->method('prepareMessage');

        $subject->notify();
    }

    /**
     * @test
     * @dataProvider notificationDataProvider
     */
    public function nofityWillCallSetBodyOnTYPO39($useHtml)
    {
        $subject = $this->createPartialMock(EmailNotification::class, ['isBelow10', 'prepareMessage', 'getNotificationMessage']);

        $mockedMail = $this->createPartialMock(MailMessage::class, ['send', 'setBody', 'setFrom', 'setTo', 'setSubject']);
        $this->inject($subject, 'mailMessage', $mockedMail);

        $subject->expects($this->once())->method('getNotificationMessage')->willReturn('');

        $subject
            ->expects($this->once())
            ->method('isBelow10')
            ->willReturn(true);

        if ($useHtml) {
            $mockedMail
                ->expects($this->once())
                ->method('setBody')
                ->with('', 'text/html');
        } else {
            $mockedMail
                ->expects($this->once())
                ->method('setBody')
                ->with('', null);
        }

        $subject->notify($useHtml);
    }

    /**
     * @test
     * @dataProvider notificationDataProvider
     */
    public function nofityWillCallHtmlAndTextOnTYPO310($useHtml)
    {
        $subject = $this->createPartialMock(EmailNotification::class, ['isBelow10', 'prepareMessage', 'getNotificationMessage']);

        $mockedMail = $this->createPartialMock(MailMessage::class, ['send', 'html', 'text', 'setFrom', 'setTo', 'setSubject']);
        $this->inject($subject, 'mailMessage', $mockedMail);

        $subject->expects($this->once())->method('getNotificationMessage')->willReturn('');

        $subject
            ->expects($this->once())
            ->method('isBelow10')
            ->willReturn(false);

        $expectMethod = $useHtml ? 'html' : 'text';
        $mockedMail
            ->expects($this->once())
            ->method($expectMethod)
            ->with('');

        $subject->notify($useHtml);
    }

    /**
     * @return array
     */
    public function notificationDataProvider()
    {
        return [
            [true],// Enable html
            [false],// Disable html
        ];
    }
}
