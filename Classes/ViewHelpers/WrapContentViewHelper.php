<?php
namespace Pixelant\PxaNewsletterSubscription\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class WrapContentViewHelper
 * @package Pixelant\PxaNewsletterSubscription\ViewHelpers
 */
class WrapContentViewHelper extends AbstractViewHelper
{

    protected $escapeChildren = false;

    protected $escapeOutput = false;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('settings', 'array', 'Settings', true);
    }

    /**
     * Wrap with special classes
     *
     * @return string
     */
    public function render()
    {
        return $this->wrap(
            'outerWrap',
            $this->wrap(
                'innerWrap',
                $this->renderChildren()
            )
        );
    }

    /**
     * Wrap
     *
     * @param $wrapType
     * @param $content
     * @return string
     */
    protected function wrap($wrapType, $content)
    {
        if (is_array($this->arguments['settings'][$wrapType])) {
            if ((int)$this->arguments['settings'][$wrapType]['enabled'] === 1) {
                return sprintf(
                    '<div class="%s">%s</div>',
                    $this->arguments['settings'][$wrapType]['class'],
                    $content
                );
            }
        }

        return $content;
    }
}
