<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Domain\Repository;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class SubscriptionRepository
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
        // don't add the pid constraint
        $defaultQuerySettings->setRespectStoragePage(false);

        $this->setDefaultQuerySettings($defaultQuerySettings);
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
