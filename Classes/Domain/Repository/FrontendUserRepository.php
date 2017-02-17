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
 * Extending Frontend User Repository
 *
 * @package pxa_newsletter_subscription
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */

class FrontendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository {

	/**
	* Gets the count of frontend users with email in a pid.
	*
	* @param string $email
	* @param int $pid
	* @return int
	*/
	public function getCountByEmailAndPid($email, $pid) {

		$query = $this->createQuery();
			
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setIgnoreEnableFields(TRUE); 
		$query->getQuerySettings()->setEnableFieldsToBeIgnored(array('disabled'));

		$countUsers = $query
			->matching(
				$query->logicalAnd(
        			$query->equals('email', $email),
        			$query->equals('pid', $pid)
        		)
        	)
        	->count();
		
		return $countUsers;
	}

	/**
	* Does frontend user with email exist on page with pid.
	*
	* @param string $email
	* @param int $pid
	* @return bool
	*/
	public function doesEmailExistInPid($email, $pid) {

		$query = $this->createQuery();
			
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setIgnoreEnableFields(TRUE); 
		$query->getQuerySettings()->setEnableFieldsToBeIgnored(array('disabled'));

		$countUsers = $query
			->matching(
				$query->logicalAnd(
        			$query->equals('email', $email),
        			$query->equals('pid', $pid)
        		)
        	)
        	->count();
		
		return $countUsers > 0;
	}

	/**
	* Gets a Frontend User by uid and hash (fax)
	*
	* @param int $uid
	* @param string $hash
	* @return Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUser
	*/
	public function getUserByUidAndHash($uid, $hash) {

		$query = $this->createQuery();
			
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setIgnoreEnableFields(TRUE); 
		$query->getQuerySettings()->setEnableFieldsToBeIgnored(array('disabled'));

		$frontendUser = $query
			->matching(
				$query->logicalAnd(
        			$query->equals('uid', $uid),
        			$query->equals('fax', $hash)
        		)
        	)
        	->execute()
			->getFirst();
		
		return $frontendUser;
	}

	/**
	* Gets a sigle (first) Frontend User by email and pid
	*
	* @param string $email
	* @param int $pid
	* @return Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUser
	*/
	public function getUserByEmailAndPid($email, $pid) {

		$query = $this->createQuery();
			
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setIgnoreEnableFields(TRUE); 
		$query->getQuerySettings()->setEnableFieldsToBeIgnored(array('disabled'));

		$frontendUser = $query
			->matching(
				$query->logicalAnd(
					$query->equals('email', $email),
					$query->equals('pid', $pid)
				)
			)
			->execute()
			->getFirst();
		
		return $frontendUser;
	}

}
?>
