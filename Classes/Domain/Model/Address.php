<?php
namespace Pixelant\PxaNewsletterSubscription\Domain\Model;

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

/**
 * Extending Address
 *
 * @package pxa_newsletter_subscription
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Address extends \TYPO3\TtAddress\Domain\Model\Address
{
    /**
     * @var boolean
     */
    protected $hidden;

    /**
     * @var bool
     */
    protected $moduleSysDmailHtml = true;

    /**
     * @param boolean $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return boolean $hidden
     */
    public function isHidden()
    {
        return $this->getHidden();
    }

    /**
     * @return boolean
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param bool $moduleSysDmailHtml
     */
    public function setModuleSysDmailHtml($moduleSysDmailHtml)
    {
        $this->moduleSysDmailHtml = $moduleSysDmailHtml;
    }

    /**
     * @return bool
     */
    public function getModuleSysDmailHtml()
    {
        return $this->moduleSysDmailHtml;
    }
}
