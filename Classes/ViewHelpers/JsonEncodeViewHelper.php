<?php
namespace Pixelant\PxaNewsletterSubscription\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class JsonEncodeViewHelper
 * @package Pixelant\PxaNewsletterSubscription\ViewHelpers
 */
class JsonEncodeViewHelper extends AbstractViewHelper
{
    protected $escapeChildren = false;

    protected $escapeOutput = false;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('value', 'array', 'Array to encode', true);
    }

    /**
     * Return json
     *
     * @return string
     */
    public function render()
    {
        return json_encode($this->arguments['value']);
    }
}
