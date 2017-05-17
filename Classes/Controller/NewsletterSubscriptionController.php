<?php
namespace Pixelant\PxaNewsletterSubscription\Controller;

use Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUserGroup;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * NewsletterSubscriptionController
 */
class NewsletterSubscriptionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
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
     * persistence manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     * @inject
     */
    protected $persistenceManager;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;


    /**
     * Render form action
     *
     * @return void
     */
    public function formAction()
    {
        $this->view->assign(
            'displayNameField',
            (bool)($this->settings['formFieldNameIsMandatory'] || !$this->settings['formFieldNameHidden'])
        );
    }

    /**
     * Render confirm action
     *
     * Renders confirm result as a content element if hash parameter is set
     * @return void
     */
    public function confirmAction()
    {

        if ($this->settings['forceFormView'] == 1) {
            $this->forward('form', null, null, $this->request->getArguments());
        }

        $hash = GeneralUtility::removeXSS(GeneralUtility::_GP('hash'));
        $status = GeneralUtility::removeXSS(GeneralUtility::_GP('status'));
        $id = intval(GeneralUtility::removeXSS(GeneralUtility::_GP('hashid')));

        if (is_string($hash) && strlen(trim($hash)) > 0) {
            if ($status == 'subscribe') {
                $this->confirmSubscription($hash, $id);
            } elseif ($status == 'unsubscribe') {
                $this->unsubscribe($hash, $id);
            }
        }
    }

    /**
     * Render ajax action
     *
     * Ajax action:
     * If hash parameter is set, used to make confirmation, else return result of subscribe/unsubscribe
     * to form in formAction.
     *
     * @return void
     */
    public function ajaxAction()
    {

        $hash = GeneralUtility::removeXSS(GeneralUtility::_GP('hash'));

        if (is_string($hash) === false || strlen(trim($hash)) == 0) {
            $response = $this->runAjax();
            header('Content-type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $status = GeneralUtility::removeXSS(GeneralUtility::_GP('status'));
            $hash = GeneralUtility::removeXSS(GeneralUtility::_GP('hash'));
            $id = intval(GeneralUtility::removeXSS(GeneralUtility::_GP('hashid')));

            if ($status == 'subscribe') {
                $this->confirmSubscription($hash, $id);
            } elseif ($status == 'unsubscribe') {
                $this->unsubscribe($hash, $id);
            }
        }
    }

    /**
     * Assign view variables (formAction,)
     *
     * @return bool If setup is valid.
     */
    protected function isConfigurationValid()
    {
        $isValid = true;
        $frontendUserGroup = $this->frontendUserGroupRepository->getFrontendUserGroupByUid(
            intval($this->settings['userGroup'])
        );
        if ($frontendUserGroup === null) {
            $this->messages[] = 'Frontend Usergroup is not valid.';
            $isValid = false;
        }

        // $storagePid = intval($this->settings['saveFolder']);


        // $GLOBALS['TYPO3_CONF_VARS']['BE']['warning_email_addr']
        return $isValid;
    }

    /**
     * runs ajax
     *
     * @return array
     */
    public function runAjax()
    {
        $name = $this->getArgument('name');
        $email = $this->getArgument('email');
        $submitType = $this->getArgument('submitType');

        $pid = intval($this->settings['saveFolder']);
        $confirm = intval($this->settings['enableEmailConfirm']);
        $emailConfirmIsEnabled = intval($this->settings['enableEmailConfirm']) == 1;
        $userGroup = intval($this->settings['userGroup']);

        // Variables to store message and status
        $message = '';
        $success = false;

        // TODO: VALIDATE CONFIGURATION AND ARGUMENTS BEFORE PROCEEDING !!
        /*
        $configurationIsValid = $this->isConfigurationValid();
        if ( !$configurationIsValid ) {
            if ( $GLOBALS['BE_USER']->user['admin'] == 1 ) {
                $message = '<ul>';
                foreach ($this->messages as $no => $message) {
                    $message .=	$message
                }
                $message = '</ul>';
            } else {
                $message = LocalizationUtility::translate('error.invalid.configuration', 'pxa_newsletter_subscription');
            }
        }
        */

        // Check if email exist in pid
        $emailExist = $this->frontendUserRepository->doesEmailExistInPid($email, $pid);

        // Check what action to execute
        if ($submitType == LocalizationUtility::translate('unsubscribe', 'pxa_newsletter_subscription')) {
            // On Unsubscribe
            if (GeneralUtility::validEmail($email) === false) {
                // Not a valid email
                $message = LocalizationUtility::translate('error.invalid.email', 'pxa_newsletter_subscription');
            } else {
                if ($emailExist === false) {
                    // email doesn't exist in pid
                    $message = LocalizationUtility::translate('error.unsubscribe.not-subscribed',
                        'pxa_newsletter_subscription');
                } else {
                    if ($emailConfirmIsEnabled) {
                        // Send unsubscribe email
                        $frontendUser = $this->frontendUserRepository->getUserByEmailAndPid($email, $pid);
                        if ($frontendUser !== null) {
                            $this->sendConfirmationEmail($frontendUser->getEmail(), $frontendUser->getName(),
                                $frontendUser->getHash(), $frontendUser->getUid(), true);
                            $message = LocalizationUtility::translate('success.unsubscribe.unsubscribed-confirm',
                                'pxa_newsletter_subscription');
                            $success = true;
                        } else {
                            $message = LocalizationUtility::translate('error.subscribe.4105',
                                'pxa_newsletter_subscription');
                        }
                    } else {
                        // Set user to deleted
                        $frontendUser = $this->frontendUserRepository->getUserByEmailAndPid($email, $pid);
                        if ($frontendUser !== null) {
                            $frontendUser->setDeleted(1);
                            $this->frontendUserRepository->update($frontendUser);
                            $this->persistenceManager->persistAll();
                            if ($frontendUser->getDeleted() == true) {
                                $message = LocalizationUtility::translate('success.unsubscribe.unsubscribed-noconfirm',
                                    'pxa_newsletter_subscription');
                                $success = true;
                            } else {
                                $message = LocalizationUtility::translate('error.subscribe.4104',
                                    'pxa_newsletter_subscription');
                            }
                        } else {
                            $message = LocalizationUtility::translate('error.subscribe.4103',
                                'pxa_newsletter_subscription');
                        }
                    }
                }
            }
        } else {
            // If not Unsubscribe
            if (GeneralUtility::validEmail($email) === false) {
                // Not a valid email
                $message = LocalizationUtility::translate('error.invalid.email', 'pxa_newsletter_subscription');
            } else {
                if ($this->isNameValid($name) === false) {
                    // Not a valid name
                    $message = LocalizationUtility::translate('error.invalid.name', 'pxa_newsletter_subscription');
                } else {
                    if ($emailExist) {
                        // Check if disabled, then resend confirmation mail ?
                        // email already exist in pid
                        $message = LocalizationUtility::translate('error.subscribe.already-subscribed',
                            'pxa_newsletter_subscription');
                    } else {
                        /** @var FrontendUserGroup $frontendUserGroup */
                        $frontendUserGroup = $this->frontendUserGroupRepository->getFrontendUserGroupByUid($userGroup);
                        if ($frontendUserGroup === null) {
                            // Could not load usergroup.
                            // TODO: generate email for admin, setup invalid frontend usergroup is invalid.
                            $message = LocalizationUtility::translate('error.subscribe.4101',
                                'pxa_newsletter_subscription');
                        } else {
                            // Since name is validated and still can be empty if name isn't mandatory, set empty name from email.
                            if (strlen(trim($name)) == 0) {
                                $name = $email;
                            }
                            // Try to create feuser and store it in repository
                            $frontendUser = $this->objectManager->get(\Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUser::class);
                            $frontendUser->setAsSubscriber($pid, $email, $name, $emailConfirmIsEnabled,
                                $frontendUserGroup);

                            // Signal slot for after fe_user creation
                            $this->signalSlotDispatcher->dispatch(
                                __CLASS__,
                                'afterFeUserCreation',
                                array($frontendUser, $this)
                            );

                            $this->frontendUserRepository->add($frontendUser);
                            $this->persistenceManager->persistAll();

                            if ($frontendUser->getUid() > 0) {
                                // User was created
                                if ($emailConfirmIsEnabled) {
                                    // Send subscribe email
                                    $this->sendConfirmationEmail($frontendUser->getEmail(), $frontendUser->getName(),
                                        $frontendUser->getHash(), $frontendUser->getUid(), false);
                                    $message = LocalizationUtility::translate('success.subscribe.subscribed-confirm',
                                        'pxa_newsletter_subscription');
                                    $success = true;
                                } else {
                                    // Add user
                                    $message = LocalizationUtility::translate('success.subscribe.subscribed-noconfirm',
                                        'pxa_newsletter_subscription');
                                    $success = true;
                                }
                            } else {
                                // The feuser was not created.
                                $message = LocalizationUtility::translate('error.subscribe.4102',
                                        'pxa_newsletter_subscription') . $frontendUser->getUid();
                            }
                        }
                    }
                }
            }
        }
        return array(
            'message' => $message,
            'success' => $success
        );
    }

    /**
     * Confirms subscription
     *
     * @param string $hash
     * @param string $id
     * @return void
     */
    protected function confirmSubscription($hash, $id)
    {

        $status = true;

        try {

            $frontendUser = $this->frontendUserRepository->getUserByUidAndHash($id, $hash);
            if ($frontendUser !== null) {
                $frontendUser->setDisable(0);
                $this->frontendUserRepository->update($frontendUser);
                $this->persistenceManager->persistAll();
                $message = LocalizationUtility::translate('subscribe_ok', 'pxa_newsletter_subscription');
            }

        } catch (\Exception $e) {

        }

        if (!isset($message)) {
            $message = LocalizationUtility::translate('subscribe_error', 'pxa_newsletter_subscription');
            $status = false;
        }

        $this->view->assign('message', $message);
        $this->view->assign('status', $status);
    }

    /**
     * Unsubscribe
     *
     * @param string $hash
     * @param string $id
     * @return void
     */
    protected function unsubscribe($hash, $id)
    {
        $status = true;

        try {
            $frontendUser = $this->frontendUserRepository->getUserByUidAndHash($id, $hash);
            if ($frontendUser !== null) {
                $frontendUser->setDeleted(1);
                $this->frontendUserRepository->update($frontendUser);
                $this->persistenceManager->persistAll();
                $message = LocalizationUtility::translate('unsubscribe_ok', 'pxa_newsletter_subscription');
            }
        } catch (\Exception $e) {

        }

        if (!isset($message)) {
            $message = LocalizationUtility::translate('unsubscribe_error', 'pxa_newsletter_subscription');
            $status = false;
        }

        $this->view->assign('message', $message);
        $this->view->assign('status', $status);
    }

    /**
     * Sends a confirmation mail
     *
     * @param string $email Email
     * @param string $name Name
     * @param string $hash Frontenduser computed hash
     * @param int $id Frontenduser id
     * @param bool $unsubscribeMail If the mail is only a unsubscribe mail
     * @return bool
     */
    protected function sendConfirmationEmail($email, $name, $hash, $id, $unsubscribeMail)
    {

        try {
            $mail = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
            $mail->setFrom($this->getConfirmMailFrom());
            $mail->setTo($this->getConfirmMailTo($name, $email));
            $mail->setSubject($this->getConfirmMailSubject());
            $mail->setBody($this->getConfirmMailBody($name, $hash, $id, $unsubscribeMail), 'text/plain');

            if (GeneralUtility::validEmail($this->settings['confirmMailReplyTo']) === true) {
                $mail->setReplyTo($this->settings['confirmMailReplyTo']);
            }

            $mail->send();

            return $mail->isSent();

        } catch (\Exception $e) {

            return false;

        }


    }

    /**
     * Generates a link to frontend either to subscribe or unsubscribe.
     *
     * Also, if flexform setting Confirm Page is set, the link is to a page, otherwise it is a ajax link.
     *
     * @param int $id Frontenduser id
     * @param string $hash Frontenduser computed hash
     * @param bool $unsubscribeLink If true, link is to unsubscribe, default is to subscribe
     * @return string
     */
    protected function getFeLink($id, $hash, $unsubscribeLink)
    {

        $mode = $unsubscribeLink ? 'unsubscribe' : 'subscribe';
        $confirmPageId = intval($this->settings['confirmPage']);

        $linkParams = array(
            "status" => $mode,
            "hashid" => $id,
            "hash" => $hash,
        );

        if ($confirmPageId > 0) {

            $feLink = $this
                ->uriBuilder
                ->reset()
                ->setTargetPageUid($confirmPageId)
                ->setArguments($linkParams)
                ->setNoCache(1)
                ->setUseCacheHash(true)
                ->setCreateAbsoluteUri(true)
                ->uriFor('confirm', null, 'NewsletterSubscription');

        } else {

            $feLink = $this
                ->uriBuilder
                ->reset()
                ->setArguments($linkParams)
                ->setNoCache(1)
                ->setUseCacheHash(true)
                ->setCreateAbsoluteUri(true)
                ->uriFor('ajax', null, 'NewsletterSubscription');

        }

        return $feLink;
    }

    /**
     * Generates the t3lib_mail_Message setFrom array for confirmation mail
     *
     * @return array
     */
    protected function getConfirmMailFrom()
    {

        // Default to Install tool default settings
        $confirmMailSenderName = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'];
        $confirmMailSenderEmail = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];

        // Override with flexform settings if set and valid
        if (is_string($this->settings['confirmMailSenderName']) && strlen(trim($this->settings['confirmMailSenderName'])) > 0) {
            $confirmMailSenderName = $this->settings['confirmMailSenderName'];
        }
        if (GeneralUtility::validEmail($this->settings['confirmMailSenderEmail'])) {
            $confirmMailSenderEmail = $this->settings['confirmMailSenderEmail'];
        }

        // If from email is still empty, use a no-reply address
        if (strlen($confirmMailSenderEmail) == 0) {
            // Won't work on all domains!
            $domain = GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');
            if (substr($domain, 0, 3) == 'www') {
                $domain = substr($domain, 4);
                $confirmMailSenderEmail = 'no-reply@' . $domain;
            } else {
                $confirmMailSenderEmail = 'no-reply@' . $domain;
            }
            $confirmMailSenderName = $confirmMailSenderEmail;
        }

        if (strlen($confirmMailSenderName) == 0) {
            $confirmMailSenderName = $confirmMailSenderEmail;
        }

        if (preg_match('/\\s|,/', $confirmMailSenderName) >= 1) {
            $confirmMailSenderName = '"' . $confirmMailSenderName . '"';
        }

        return array($confirmMailSenderEmail => $confirmMailSenderName);

    }

    /**
     * Generates the t3lib_mail_Message setTo array for confirmation mail
     *
     * @param string $name Name
     * @param string $email Email
     * @return array
     */
    protected function getConfirmMailTo($name, $email)
    {

        // Set defaults, name same as email
        $confirmMailRecipientName = $email;
        $confirmMailRecipientEmail = $email;

        // If name is set, use it
        if (is_string($name) && strlen(trim($name)) > 0) {
            $confirmMailRecipientName = $name;
        }

        if (preg_match('/\\s|,/', $confirmMailRecipientName) >= 1) {
            $confirmMailRecipientName = '"' . $confirmMailRecipientName . '"';
        }

        return array($confirmMailRecipientEmail => $confirmMailRecipientName);
    }

    /**
     * Generates the t3lib_mail_Message setSubject string for confirmation mail
     *
     * @return string
     */
    protected function getConfirmMailSubject()
    {

        // Set defaults subject from translation
        $subject = LocalizationUtility::translate('confirm_mail_subject', 'pxa_newsletter_subscription');

        // Override with flexform settings if set and valid
        if (is_string($this->settings['confirmMailSubject']) && strlen(trim($this->settings['confirmMailSubject'])) > 0) {
            $subject = $this->settings['confirmMailSubject'];
        }
        return $subject;

    }

    /**
     * Generates the t3lib_mail_Message setBody string for confirmation mail
     *
     * @param string $name Name
     * @param string $hash Frontenduser computed hash
     * @param int $id Frontenduser id
     * @param bool $unsubscribeMail If the mail is only a unsubscribe mail
     * @return string
     */
    protected function getConfirmMailBody($name, $hash, $id, $unsubscribeMail)
    {

        $subscribeLink = $this->getFeLink($id, $hash, false);
        $unsubscribeLink = $this->getFeLink($id, $hash, true);

        // Set defaults from original translation, has replacement in texts
        $bodyText = LocalizationUtility::translate('confirm_mail_greeting', 'pxa_newsletter_subscription',
                array($name)) . PHP_EOL . PHP_EOL;
        $bodySubscribeLink = LocalizationUtility::translate('confirm_mail_line1', 'pxa_newsletter_subscription',
            array(PHP_EOL . PHP_EOL . $subscribeLink . PHP_EOL . PHP_EOL));
        $bodyUnsubscribeLink = LocalizationUtility::translate('confirm_mail_line2', 'pxa_newsletter_subscription',
            array(PHP_EOL . PHP_EOL . $unsubscribeLink . PHP_EOL . PHP_EOL));
        $bodyFooter = '';

        // Override with flexform values if set
        if ($unsubscribeMail) {
            if (is_string($this->settings['confirmMailUnsubscribeBody']) && strlen(trim($this->settings['confirmMailUnsubscribeBody'])) > 0) {
                $bodyText = $this->settings['confirmMailUnsubscribeBody'] . PHP_EOL . PHP_EOL;
            }
        } else {
            if (is_string($this->settings['confirmMailSubscribeBody']) && strlen(trim($this->settings['confirmMailSubscribeBody'])) > 0) {
                $bodyText = $this->settings['confirmMailSubscribeBody'] . PHP_EOL . PHP_EOL;
            }
        }

        if (is_string($this->settings['confirmMailSubscribeInstruction']) && strlen(trim($this->settings['confirmMailSubscribeInstruction'])) > 0) {
            $bodySubscribeLink = $this->settings['confirmMailSubscribeInstruction'];
            $bodySubscribeLink .= PHP_EOL . $subscribeLink . PHP_EOL . PHP_EOL;
        }

        if (is_string($this->settings['confirmMailUnsubscribeInstruction']) && strlen(trim($this->settings['confirmMailUnsubscribeInstruction'])) > 0) {
            $bodyUnsubscribeLink = $this->settings['confirmMailUnsubscribeInstruction'];
            $bodyUnsubscribeLink .= PHP_EOL . $unsubscribeLink . PHP_EOL . PHP_EOL;
        }

        if (is_string($this->settings['confirmMailFooter']) && strlen(trim($this->settings['confirmMailFooter'])) > 0) {
            $bodyFooter = PHP_EOL . PHP_EOL . $this->settings['confirmMailFooter'];
        }
        // Remove subscribe link part of message if it is a unsubscribe mail.
        if ($unsubscribeMail) {
            $bodySubscribeLink = '';
        }

        return $bodyText . $bodySubscribeLink . $bodyUnsubscribeLink . $bodyFooter;

    }

    /**
     * Get request argument
     *
     * @var string $argument Name of argument
     * @return string
     */
    protected function getArgument($argument)
    {

        $return = '';
        if ($this->request->hasArgument($argument)) {
            $return = GeneralUtility::removeXSS($this->request->getArgument($argument));
        }
        return $return;
    }

    /**
     * Check if name is valid.
     *
     * @var string $name Name
     * @return bool
     */
    protected function isNameValid($name)
    {
        $isValid = false;
        if ($this->settings['formFieldNameIsMandatory'] == 0) {
            $isValid = true;
        } else {
            if (is_string($name) && strlen(trim($name)) > 0) {
                $isValid = true;
            }
        }
        return $isValid;
    }

    /**
     * Check if name field should be hidden in form
     *
     * @return bool
     */
    protected function isNameVisibleInForm()
    {
        $isVisible = true;
        if ($this->settings['formFieldNameIsMandatory'] == 0) {
            if ($this->settings['formFieldNameHidden'] == 1) {
                $isVisible = false;
            }
        }
        return $isVisible;
    }
}
