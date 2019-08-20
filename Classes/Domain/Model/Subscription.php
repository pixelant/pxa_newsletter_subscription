<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Subscription
 * @package Pixelant\PxaNewsletterSubscription\Domain\Model
 */
class Subscription extends AbstractEntity
{
    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
