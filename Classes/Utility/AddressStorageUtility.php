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

use Pixelant\PxaNewsletterSubscription\Domain\Model\Address;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Abstract Storage Utility
 *
 * @package pxa_newsletter_subscription
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class AddressStorageUtility extends \Pixelant\PxaNewsletterSubscription\Utility\AbstractStorageUtility
{
    /**
     * addressRepository
     *
     * @var \Pixelant\PxaNewsletterSubscription\Domain\Repository\AddressRepository
     */
    protected $addressRepository;

    /**
     * @param \Pixelant\PxaNewsletterSubscription\Domain\Repository\AddressRepository $addressRepository
     */
    public function injectAddressRepository(
        \Pixelant\PxaNewsletterSubscription\Domain\Repository\AddressRepository $addressRepository
    ) {
        $this->addressRepository = $addressRepository;

        $pid = intval($this->settings['saveFolder']);

        if ($pid) {
            $querySettings = $this->addressRepository->createQuery()->getQuerySettings();
            $querySettings->setStoragePageIds([$pid]);
            $querySettings->setIgnoreEnableFields(true);
            $querySettings->setEnableFieldsToBeIgnored([]);
            $this->addressRepository->setDefaultQuerySettings($querySettings);
        }
    }

    /**
     * Check if email already exists
     *
     * @param string $email
     *
     * @return Address|null
     */
    public function getSubscriber($email)
    {
        $address = $this->addressRepository->findOneByEmail($email);

        return $address;
    }

    /**
     * Process subscribe action
     *
     * @param $arguments
     *
     * @return Address
     */
    public function processSubscription($arguments)
    {
        $pid = intval($this->settings['saveFolder']);

        // Since name is validated and still can be empty if name isn't mandatory, set empty name from email.
        $name = empty($arguments['name']) ? $arguments['email'] : $arguments['name'];

        /** @var Address $address */
        $address = $this->objectManager->get(Address::class);

        $address->setPid($pid);
        $address->setEmail($arguments['email']);
        $address->setName($name);
        $address->setHidden($this->settings['enableEmailConfirm']);

        // Signal slot for after fe_user creation
        $this->signalSlotDispatcher->dispatch(
            \Pixelant\PxaNewsletterSubscription\Controller\NewsletterSubscriptionController::class,
            'afterAddressCreation',
            [$address, $this]
        );

        $this->addressRepository->add($address);
        $this->persistenceManager->persistAll();

        // Send email to admin if the subscription is confirmed
        if (!$this->settings['enableEmailConfirm']) {
            $this->sendAdminEmail('subscribe', $frontendUser);
        }

        return $address;
    }

    /**
     * Process unsubscribe action
     *
     * @param Address $subscriber
     */
    public function revokeSubscription($subscriber)
    {
        $this->addressRepository->remove($subscriber);
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

        if ($this->hashService->validateHmac('pxa_newsletter_subscription-subscribe' . $id, $hash)) {
            /** @var Address $address */
            $address = $this->addressRepository->findOneByUid($id);
            if ($address !== null && $address->getHidden()) {
                $address->setHidden(false);

                $this->addressRepository->update($address);
                $this->persistenceManager->persistAll();

                $message = $this->translate('subscribe_ok');
                $status = true;

                $this->sendAdminEmail('subscribe', $address);
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

        if ($this->hashService->validateHmac('pxa_newsletter_subscription-unsubscribe' . $id, $hash)) {
            /** @var Address $address */
            $address = $this->addressRepository->findByUid($id);
            if ($address !== null) {
                $this->addressRepository->remove($address);
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
     * @param Address|null $subscriber
     * @param bool $isNewSubscription
     * @param array $arguments
     *
     * @return string Empty if no error
     */
    public function validateSubscription(
        Address $subscriber = null,
        $isNewSubscription,
        $arguments
    ) {
        $message = '';

        if ($isNewSubscription && $subscriber) {
            $message = $this->translate('error.subscribe.already-subscribed');
        } elseif ($isNewSubscription && !$this->isNameValid($arguments['name'])) {
            $message = $this->translate('error.invalid.name');
        } elseif (!$isNewSubscription && !$subscriber) {
            $message = $this->translate('error.unsubscribe.not-subscribed');
        }

        return $message;
    }

    public function getEmailBody($subscriber)
    {
        $result = '<div>';
        $result .= '<p>' . 'Name: ' . $subscriber->getName() . '</p>';
        $result .= '</div>';
        return $result;
    }
}
