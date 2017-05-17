<?php
namespace Pixelant\PxaNewsletterSubscription\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Inna Titorenko <inna@pixelant.se>, Pixelant
 *  (c) 2013 Jozef Spisiak <jozef@pixelant.se>, Pixelant
 *  (c) 2014 Mats Svensson <mats@pixelant.se>, Pixelant
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

/**
 * Extending Frontend User
 *
 * @package pxa_newsletter_subscription
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */

class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
{

    /**
     * @var boolean
     */
    protected $disable;

    /**
     * @var boolean
     */
    protected $deleted;

    /**
     * @param boolean $disable
     */
    public function setDisable($disable)
    {
        $this->disable = $disable;
    }

    /**
     * @return boolean
     */
    public function getDisable()
    {
        return $this->disable;
    }

    /**
     * @param boolean $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Sets password with a random generated password.
     *
     * @param int $length The length of the password.
     * @return void
     */
    public function setRandomPassword($length = 12)
    {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr(str_shuffle($chars), 0, $length);

        $this->password = $password;
    }

    /**
     * Sets random hash (uses fax property).
     *
     * @return void
     */
    public function setHash()
    {
        $randomHash = substr(md5(uniqid(rand(), true)), 16, 16);
        $this->fax = $randomHash;
    }

    /**
     * Gets random hash (uses fax property).
     *
     * @return string
     */
    public function getHash()
    {
        return $this->fax;
    }

    /**
     * Creates a new Frontend User as a subscriber.
     *
     * @param int $pid The page to store the Frontend User on.
     * @param string $email The email of the user.
     * @param string $name The name of the user.
     * @param bool $confirm If the user needs to confirm subscription by email.
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup The usergroup user should be a member of.
     * @return void
     */
    public function setAsSubscriber(
        $pid,
        $email,
        $name,
        $confirm,
        \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup
    ) {

        $this->setPid(intval($pid));
        $this->setUsername($email);
        $this->setEmail($email);
        $this->setName($name);
        $this->setDisable(intval($confirm));
        $this->setHash();
        $this->setRandomPassword(12);
        $this->addUsergroup($usergroup);
    }
}
