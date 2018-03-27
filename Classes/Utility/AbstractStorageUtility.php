<?php

namespace Pixelant\PxaNewsletterSubscription\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Daniel Lorenz <daniel.lorenz@tritum.de>, TRITUM GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUser;
use Pixelant\PxaNewsletterSubscription\Domain\Model\Address;

use Pixelant\PxaNewsletterSubscription\Service\AdminNotificationService;

use Pixelant\PxaNewsletterSubscription\Service\HashService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract Storage Utility
 *
 * @package pxa_newsletter_subscription
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
abstract class AbstractStorageUtility
{
    /**
     * Object Manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * persistence manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

    /**
     * Hash Service
     *
     * @var HashService
     */
    protected $hashService;

    /**
     * @var array
     */
    protected $settings = [];

    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(
        \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(
        \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
    ) {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @param HashService $hashService
     */
    public function injectHashService(
        HashService $hashService
    ) {
        $this->hashService = $hashService;
    }

    /**
     * Check if email already exists
     *
     * @param string $email
     *
     * @return FrontendUser|Address|null
     */
    abstract public function getSubscriber($email);

    /**
     * Process subscribe action
     *
     * @param array $arguments
     *
     * @return array
     */
    abstract public function processSubscription($arguments);

    /**
     * Process unsubscribe action
     *
     * @param FrontendUser|Address|null $subscriber
     */
    abstract public function revokeSubscription($subscriber);

    /**
     * Confirms subscription
     *
     * @param string $hash
     * @param string $id
     *
     * @return array
     */
    abstract public function confirmSubscription($hash, $id);

    /**
     * Unsubscribe
     *
     * @param string $hash
     * @param string $id
     *
     * @return void
     */
    abstract public function confirmUnsubscription($hash, $id);

    /**
     * Gets the info for the subscription for admin email
     * @param FrontendUser|Address|null $subscriber
     * @return string
     */
    abstract public function getEmailBody($subscriber);

    /**
     * Translate label
     *
     * @param string $key
     * @return NULL|string
     */
    protected function translate($key = '')
    {
        return LocalizationUtility::translate($key, 'pxa_newsletter_subscription');
    }

    /**
     * Generates a link to frontend either to subscribe or unsubscribe.
     *
     * Also, if flexform setting Confirm Page is set, the link is to a page, otherwise it is a ajax link.
     *
     * @param int $id Frontenduser id
     * @param string $status Subscribe or unsubscribe
     * @return string
     */
    protected function getFeLink($id, $status)
    {
        $confirmPageId = intval($this->settings['confirmPage']) ?
            intval($this->settings['confirmPage']) : $GLOBALS['TSFE']->id;

        $linkParams = [
            'status' => $status,
            'hashid' => $id,
            'hash' => $this->hashService->generateHmac('pxa_newsletter_subscription-' . $status . $id)
        ];


        return $this
            ->uriBuilder
            ->reset()
            ->setTargetPageUid($confirmPageId)
            ->setCreateAbsoluteUri(true)
            ->uriFor('confirm', $linkParams);
    }

    /**
     * Check if name is valid.
     *
     * @var string $name Name
     * @return bool
     */
    protected function isNameValid($name)
    {
        return !$this->settings['formFieldNameIsMandatory'] || !empty($name);
    }

    /**
     * @param $type string
     * @param FrontendUser|Address|null $subscriber
     */
    protected function sendAdminEmail($type, $subscriber)
    {
        // Exit early if not configured
        if (empty($this->settings['adminNotificationSettings']['receiverEmail'])) {
            return;
        }

        $adminNotificationService = GeneralUtility::makeInstance(
            AdminNotificationService::class,
            $this->settings['adminNotificationSettings'],
            $type
        );

        $adminNotificationService->sendNotification($this, $subscriber);
    }
}
