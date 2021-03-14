<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TidierListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * This goes through the test case and unsets any properties that have been set on the class.
     */
    public function endTest(Test $test, float $time): void
    {
        if (false === ($test instanceof WebTestCase)) {
            // We only care about inspecting if this is a WebTestCase test
            return;
        }

        self::stripProperties($test);
    }

    public static function stripProperties(WebTestCase $target): void
    {
        $refl = new \ReflectionObject($target);
        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== \strncmp($prop->getDeclaringClass()->getName(), 'PHPUnit_', 8)) {
                $prop->setAccessible(true);
                $prop->setValue($target, null);
            }
        }
    }
}
