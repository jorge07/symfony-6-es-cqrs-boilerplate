<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Domain\User\Event\UserWasCreated;

return static function(ContainerConfigurator $configurator) {
    $configurator->parameters()
        /**
         * Map domain events to serialization contexts.
         * This allows us to hook into Broadway serialization process.
         * Note that if a mapping doesn't exist, it will serialize the whole object.
         *
         * @see \App\Infrastructure\Share\Serializer\BroadwaySerializer
         */
        ->set('normalization_contexts', [
            UserWasCreated::class => [
                'groups' => ['user_was_created', 'credentials_sensitive']
            ],
        ])
        ->set('denormalization_contexts', [
            UserWasCreated::class => [
                'groups' => ['user_was_created', 'credentials_sensitive']
            ],
        ])
    ;

    $configurator->services()
        ->defaults()
            ->bind('array $normalizationContexts', '%normalization_contexts%')
            ->bind('array $denormalizationContexts', '%denormalization_contexts%')
    ;
};