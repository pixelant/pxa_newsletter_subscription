<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlexFormSettingsService
 * @package Pixelant\PxaNewsletterSubscription\Service
 */
class FlexFormSettingsService implements SingletonInterface
{
    /**
     * Keep content uid to it flexform settings
     * @var array
     */
    protected $settings = [];

    /**
     * Get flexform as array by content uid
     *
     * @param int $ceUid
     * @return array|null
     */
    public function getFlexFormArray(int $ceUid): ?array
    {
        if (isset($this->settings[$ceUid])) {
            return $this->settings[$ceUid];
        }

        $flexFormContent = $this->getFlexFormContent($ceUid);

        if (!empty($flexFormContent)) {
            $result = $this->getFlexFormService()->convertFlexFormContentToArray($flexFormContent);
            $this->settings[$ceUid] = $result;

            return $result;
        }

        return null;
    }

    /**
     * Get flexform content from DB by content UID
     *
     * @param int $ceUid
     * @return string|null
     */
    protected function getFlexFormContent(int $ceUid): ?string
    {
        $flexFormContent = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content')
            ->select(
                ['pi_flexform'],
                'tt_content',
                ['uid' => $ceUid]
            )
            ->fetchColumn(0);

        return $flexFormContent !== false ? $flexFormContent : null;
    }

    /**
     * @return FlexFormService
     */
    protected function getFlexFormService(): FlexFormService
    {
        return GeneralUtility::makeInstance(FlexFormService::class);
    }
}