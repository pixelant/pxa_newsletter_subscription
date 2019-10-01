<?php

namespace Pixelant\PxaNewsletterSubscription\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pixelant\PxaNewsletterSubscription\Service\FlexFormSettingsService;
use TYPO3\CMS\Core\Service\FlexFormService;

/**
 * Class FlexFormSettingsServiceTest
 * @package Pixelant\PxaNewsletterSubscription\Tests\Unit\Service
 */
class FlexFormSettingsServiceTest extends UnitTestCase
{
    /**
     * @var FlexFormSettingsService
     */
    protected $subject = null;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->subject = $this->getMockBuilder(FlexFormSettingsService::class)
            ->setMethods(['getFlexFormContent', 'getFlexFormService'])
            ->getMock();
    }

    /**
     * @test
     */
    public function getFlexFormArrayTryToGenerateSettingsIfNotInCache()
    {
        $mockedFlexFormService = $this->createMock(FlexFormService::class);
        $mockedFlexFormService->expects($this->once())->method('convertFlexFormContentToArray')->willReturn([]);

        $this->subject
            ->expects($this->once())
            ->method('getFlexFormService')
            ->willReturn($mockedFlexFormService);

        $this->subject->getFlexFormArray(10);
    }

    /**
     * @test
     */
    public function getFlexFormArrayWillNotGenerateSettingsIfSetInCacheAndReturnCache()
    {
        $ceId = 10;
        $settings = [
            $ceId => [
                'test' => true
            ]
        ];

        $this->inject($this->subject, 'settings', $settings);

        $this->subject
            ->expects($this->never())
            ->method('getFlexFormService');

        $this->subject
            ->expects($this->never())
            ->method('getFlexFormContent');

        $result = $this->subject->getFlexFormArray($ceId);

        $this->assertEquals($settings[$ceId], $result);
    }
}
