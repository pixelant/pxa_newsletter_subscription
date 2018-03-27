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
use Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUserGroup;

/**
 * Abstract Storage Utility
 *
 * @package pxa_newsletter_subscription
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FrontendUserStorageUtility extends \Pixelant\PxaNewsletterSubscription\Utility\AbstractStorageUtility
{
    /**
     * frontendUserRepository
     *
     * @var \Pixelant\PxaNewsletterSubscription\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * frontendUserGroupRepository
     *
     * @var \Pixelant\PxaNewsletterSubscription\Domain\Repository\FrontendUserGroupRepository
     * @inject
     */
    protected $frontendUserGroupRepository;

    /**
     * @param \Pixelant\PxaNewsletterSubscription\Domain\Repository\FrontendUserRepository $frontendUserRepository
     */
    public function injectFrontendUserRepository(
        \Pixelant\PxaNewsletterSubscription\Domain\Repository\FrontendUserRepository $frontendUserRepository
    ) {
        $this->frontendUserRepository = $frontendUserRepository;

        $pid = intval($this->settings['saveFolder']);

        if ($pid) {
            $querySettings = $this->frontendUserRepository->createQuery()->getQuerySettings();
            $querySettings->setStoragePageIds([$pid]);
            $this->frontendUserRepository->setDefaultQuerySettings($querySettings);
        }
    }

    /**
     * Check if email already exists
     *
     * @param string $email
     *
     * @return FrontendUser|null
     */
    public function getSubscriber($email)
    {
        $frontendUser = $this->frontendUserRepository->findOneByEmail($email);

        return $frontendUser;
    }

    /**
     * Process subscribe action
     *
     * @param array $arguments
     *
     * @return FrontendUser
     */
    public function processSubscription($arguments)
    {
        $pid = intval($this->settings['saveFolder']);

        // Since name is validated and still can be empty if name isn't mandatory, set empty name from email.
        $name = empty($arguments['name']) ? $arguments['email'] : $arguments['name'];

        /** @var FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->getFrontendUserGroupByUid(
            $this->settings['userGroup']
        );

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->objectManager->get(FrontendUser::class);
        $frontendUser->setAsSubscriber(
            $pid,
            $arguments['email'],
            $name,
            $this->settings['enableEmailConfirm'],
            $frontendUserGroup
        );

        // Signal slot for after fe_user creation
        $this->signalSlotDispatcher->dispatch(
            \Pixelant\PxaNewsletterSubscription\Controller\NewsletterSubscriptionController::class,
            'afterFeUserCreation',
            [$frontendUser, $this]
        );

        $this->frontendUserRepository->add($frontendUser);
        $this->persistenceManager->persistAll();

        // Send email to admin if the subscription is confirmed
        if (!$this->settings['enableEmailConfirm']) {
            $this->sendAdminEmail('subscribe', $frontendUser);
        }

        return $frontendUser;
    }

    /**
     * Process unsubscribe action
     *
     * @param FrontendUser $subscriber
     */
    public function revokeSubscription($subscriber)
    {
        $this->frontendUserRepository->remove($subscriber);
        $this->persistenceManager->persistAll();

        $this->sendAdminEmail('unsubscribe', $subscriber);
    }

    /**
     * Confirms subscription
     *
     * @param string $hash
     * @param string $id
     *
     * @return array
     */
    public function confirmSubscription($hash, $id)
    {
        $message = $this->translate('subscribe_error');
        $status = false;

        if ($this->hashService->validateSubscriptionHash($id, $hash)) {
            /** @var FrontendUser $frontendUser */
            $frontendUser = $this->frontendUserRepository->findOneByUid($id);
            if ($frontendUser !== null && $frontendUser->getDisable()) {
                $frontendUser->setDisable(false);

                $this->frontendUserRepository->update($frontendUser);
                $this->persistenceManager->persistAll();

                $message = $this->translate('subscribe_ok');
                $status = true;

                $this->sendAdminEmail('subscribe', $frontendUser);
            }
        }

        return [$message, $status];
    }

    /**
     * Unsubscribe
     *
     * @param string $hash
     * @param string $id
     *
     * @return array
     */
    public function confirmUnsubscription($hash, $id)
    {
        $message = $this->translate('unsubscribe_error');
        $status = false;

        if ($this->hashService->validateSubscriptionHash($id, $hash)) {
            $frontendUser = $this->frontendUserRepository->findByUid($id);

            if ($frontendUser !== null) {
                $this->frontendUserRepository->remove($frontendUser);
                $this->persistenceManager->persistAll();

                $message = $this->translate('unsubscribe_ok');
                $status = true;
            }
        }

        return [$message, $status];
    }

    /**
     * Check if data is valid
     *
     * @param FrontendUser|null $subscriber
     * @param bool $isNewSubscription
     * @param array $arguments
     *
     * @return string Empty if no error
     */
    public function validateSubscription(
        FrontendUser $subscriber = null,
        $isNewSubscription,
        $arguments
    ) {
        $message = '';

        if ($isNewSubscription && $subscriber) {
            $message = $this->translate('error.subscribe.already-subscribed');
        } elseif ($isNewSubscription && !$this->isNameValid($arguments['name'])) {
            $message = $this->translate('error.invalid.name');
        } elseif ($isNewSubscription && is_null($this->frontendUserGroupRepository->getFrontendUserGroupByUid($this->settings['userGroup']))) {
            $message = $this->translate('error.subscribe.4101');
        } elseif (!$isNewSubscription && !$subscriber) {
            $message = $this->translate('error.unsubscribe.not-subscribed');
        }

        return $message;
    }

    public function getEmailBody($subscriber)
    {
        $result = '<div>';
        $result .= '<p>' . 'Username: ' . $subscriber->getUsername() . '</p>';
        $result .= '<p>' . 'Name: ' . $subscriber->getName() . '</p>';
        $result .= '</div>';
        return $result;
    }
}
