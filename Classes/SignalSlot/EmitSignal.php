<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\SignalSlot;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Trait EmitSignal
 * @package Pixelant\PxaNewsletterSubscription\Controller\Traits
 */
trait EmitSignal
{
    /**
     * Emit signal
     *
     * @param string $name
     * @param array $variables
     * @return array
     */
    protected function emitSignal(string $name, ...$variables): array
    {
        $class = get_class($this);

        return $this->getSignalSlotDispatcher()->dispatch(
            $class,
            $name,
            $variables
        );
    }

    /**
     * @return Dispatcher
     */
    protected function getSignalSlotDispatcher(): Dispatcher
    {
        if (property_exists($this, 'signalSlotDispatcher') && $this->signalSlotDispatcher !== null) {
            return $this->signalSlotDispatcher;
        }

        return GeneralUtility::makeInstance(Dispatcher::class);
    }
}
