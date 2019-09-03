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
     * @param string $class
     * @param string $name
     * @param array $variables
     * @return array
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    protected function emitSignal(string $class, string $name, ...$variables): array
    {
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
