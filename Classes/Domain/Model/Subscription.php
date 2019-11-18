<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Subscription representing newsletter subscriptions
 *
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
     * Field is not persisted in DB
     * Just for validation
     *
     * @var bool
     */
    protected $acceptTerms = false;

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

    /**
     * @return bool
     */
    public function isAcceptTerms(): bool
    {
        return $this->acceptTerms;
    }

    /**
     * @param bool $acceptTerms
     */
    public function setAcceptTerms(bool $acceptTerms): void
    {
        $this->acceptTerms = $acceptTerms;
    }
}
