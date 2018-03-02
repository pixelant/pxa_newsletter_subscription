<?php
namespace Pixelant\PxaNewsletterSubscription\Domain\Repository;

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
 * Extending Frontend User Group Repository
 *
 * @package pxa_newsletter_subscription
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FrontendUserGroupRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
{
    /**
     * Gets a Frontend User Group by uid without respecting storage page.
     *
     * @param int $uid
     * @return object
     */
    public function getFrontendUserGroupByUid($uid)
    {

        $query = $this->createQuery();

        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query
            ->matching(
                $query->equals('uid', $uid)
            )
            ->execute()
            ->getFirst();
    }
}
