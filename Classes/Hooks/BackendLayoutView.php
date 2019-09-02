<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Hooks;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

            $output = $this->generateOutput($settings);

            if (!empty($output)) {
                return '<hr><pre>' . $output . '</pre>';
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
    protected function generateOutput(array $settings): string
    {
        $output = '';

        if ($settings['switchableControllerActions'] === 'NewsletterSubscription->form;NewsletterSubscription->confirm') {
            foreach ($settings['settings'] as $field => $value) {
                $title = $this->getTitleOfField($field);
                $value = $this->getValueOfField($field, $value);

                $output .= sprintf('<b>%s</b>: %s<br>', $title, $value);
            }
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
        if (empty($value)) {
            return $this->translate('flexform.no_value');
        }

        switch ($field) {
            case 'notifySubscriber':
            case 'enableEmailConfirmation':
            case 'nameIsMandatory':
                $value = $this->translate($value ? 'flexform.yes' : 'flexform.no');
                break;
        }

        return $value;
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
