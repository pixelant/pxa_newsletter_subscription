<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Domain\Repository;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for newsletter subscriptions
 * @package Pixelant\PxaNewsletterSubscription\Domain\Repository
 */
class SubscriptionRepository extends Repository
{
    /**
     * Default query settings
     */
    public function initializeObject()
    {
        /** @var $defaultQuerySettings Typo3QuerySettings */
        $defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        // Remove the pid constraint
        $defaultQuerySettings->setRespectStoragePage(false);

        $this->setDefaultQuerySettings($defaultQuerySettings);
    }

    /**
     * Find hidden subscription
     *
     * @param int $uid
     * @return Subscription|null
     */
    public function findByUidHidden(int $uid): ?Subscription
    {
        $query = $this->createQuery();

        $query->getQuerySettings()
            ->setRespectStoragePage(false)
            ->setRespectSysLanguage(false)
            ->setIgnoreEnableFields(true)
            ->setEnableFieldsToBeIgnored(['disabled']);

        $query->matching(
            $query->equals('uid', $uid)
        );

        return $query->execute()->getFirst();
    }

    /**
     * Find hidden by email and pid
     *
     * @param string $email
     * @param int $pid
     * @return Subscription|null
     */
    public function findByEmailAndPidHidden(string $email, int $pid): ?Subscription
    {
        $query = $this->createQuery();
        $query
            ->getQuerySettings()
            ->setIgnoreEnableFields(true)
            ->setEnableFieldsToBeIgnored(['disabled']);

        $query->matching($query->logicalAnd([
            $query->equals('email', $email),
            $query->equals('pid', $pid)
        ]));

        return $query->execute()->getFirst();
    }

    /**
     * Find by email in storage
     *
     * @param string $email
     * @param int $pid
     * @return Subscription|null
     */
    public function findByEmailAndPid(string $email, int $pid): ?Subscription
    {
        $query = $this->createQuery();

        $query->matching($query->logicalAnd([
            $query->equals('email', $email),
            $query->equals('pid', $pid)
        ]));

        return $query->execute()->getFirst();
    }
}
