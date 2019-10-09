<?php
declare(strict_types=1);

return [
    Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription::class => [
        'tableName' => 'fe_users',
        'properties' => [
            'hidden' => [
                'fieldName' => 'disable',
            ],
        ],
    ],
];
