<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Hooks;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * Class BackendLayoutView
 * @package Pixelant\PxaNewsletterSubscription\Hooks
 */
class BackendLayoutView
{
    /**
     * Path to lang file
     *
     * @var string
     */
    protected static $LL = 'LLL:EXT:pxa_newsletter_subscription/Resources/Private/Language/locallang_be.xlf:';

    /**
     * Load extension preview in BE
     * @param array $params
     * @return string
     */
    public function getExtensionSummary(array $params): string
    {
        if (!empty($params['row']['pi_flexform'])) {
            $settings = $this->parseFlexFormSettings($params['row']['pi_flexform']);

            if (!empty($settings['switchableControllerActions'])) {
                list($mainControllerAction) = explode(';', $settings['switchableControllerActions']);
                list(, $action) = explode('->', $mainControllerAction);

                $output = sprintf(
                    '<b>%s</b><br><b>%s</b>',
                    $this->translate('plugin_name'),
                    $this->translate('action_' . $action)
                );

                $settingsOutput = $this->generateSettingsOutput($settings);
                if (!empty($settingsOutput)) {
                    $output .= '<br><br><pre>' . $settingsOutput . '</pre>';
                }

                return $output;
            }
        }

        return '';
    }

    /**
     * Generate preview of plugin settings
     *
     * @param array $settings
     * @return string
     */
    protected function generateSettingsOutput(array $settings): string
    {
        $output = '';
        $currentAction = $settings['switchableControllerActions'];

        if (StringUtility::beginsWith($currentAction, 'NewsletterSubscription->form;')) {
            foreach ($settings['settings'] as $field => $value) {
                $title = $this->getTitleOfField($field);
                $value = $this->getValueOfField($field, $value);

                if (empty($title)) {
                    continue;
                }

                $output .= sprintf('<b>%s</b>: %s<br>', $title, $value);
            }
        } elseif (StringUtility::beginsWith($currentAction, 'NewsletterSubscription->unsubscribe;')) {
            $output .= sprintf(
                '<b>%s</b>: %s<br>',
                $this->getTitleOfField('notifyAdmin'),
                $this->getValueOfField('notifyAdmin', $settings['settings']['notifyAdmin'])
            );
        }

        return $output;
    }

    /**
     * Get value for given field
     *
     * @param string $field
     * @param $value
     * @return mixed
     */
    protected function getValueOfField(string $field, $value)
    {
        if (empty($value) && $value !== '0') {
            return $this->translate('flexform.no_value');
        }

        switch ($field) {
            case 'notifyAdmin':
                $value = implode(', ', explode("\n", $value));
                break;
            case 'notifySubscriber':
            case 'enableEmailConfirmation':
            case 'nameIsMandatory':
            case 'resendConfirmationEmail':
                $value = $this->translate($value ? 'flexform.yes' : 'flexform.no');
                break;
            case 'acceptTermsLink':
            case 'confirmationPage':
            case 'storagePid':
            case 'unsubscribePage':
                $value = sprintf('%s (%s)', $this->getPageTitle($value), $value);
                break;
        }

        return $value;
    }

    /**
     * Get page title from link target or just uid
     *
     * @param string $pageLink
     * @return string
     */
    protected function getPageTitle(string $pageLink): string
    {
        if (StringUtility::beginsWith($pageLink, 't3://page?uid')) {
            list(, $pageLink) = GeneralUtility::trimExplode('=', $pageLink, true);
        }

        if (!MathUtility::canBeInterpretedAsInteger($pageLink)) {
            return $pageLink;
        }

        $title = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages')
            ->select(
                ['title'],
                'pages',
                ['uid' => (int)$pageLink]
            )
            ->fetchColumn(0);

        return $title !== false ? $title : '';
    }

    /**
     * Get title of field
     *
     * @param string $fieldName
     * @return string
     */
    protected function getTitleOfField(string $fieldName): string
    {
        $fieldName = 'flexform.' . GeneralUtility::camelCaseToLowerCaseUnderscored($fieldName);

        foreach ([$fieldName . '_title', $fieldName] as $titleVariant) {
            $title = $this->translate($titleVariant);
            if (!empty($title)) {
                return $title;
            }
        }

        return '';
    }

    /**
     * Translate label
     *
     * @param string $key
     * @return string
     */
    protected function translate(string $key): string
    {
        return $this->getLanguageService()->sL(static::$LL . $key);
    }

    /**
     * Convert XML content to array
     *
     * @param string $flexFormContent
     * @return array
     */
    protected function parseFlexFormSettings(string $flexFormContent): array
    {
        $result = $this->getFlexFormService()->convertFlexFormContentToArray($flexFormContent);
        return $result;
    }

    /**
     * @return FlexFormService
     */
    protected function getFlexFormService(): FlexFormService
    {
        return GeneralUtility::makeInstance(FlexFormService::class);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
