<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->symfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml');

    $rectorConfig->paths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->sets([
        // PHP 8.2 compatibility
        LevelSetList::UP_TO_PHP_82,

        // Symfony upgrade sets
        SymfonySetList::SYMFONY_64,
        SymfonySetList::SYMFONY_70,
        SymfonySetList::SYMFONY_71,

        // Code quality improvements
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,

        // Doctrine updates
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,

        // PHPUnit 10 upgrade
        PHPUnitSetList::PHPUNIT_100,
    ]);
};
