<?php

namespace AdilKhan\PiiRedactor\Tests;

use AdilKhan\PiiRedactor\Facades\Pii;
use AdilKhan\PiiRedactor\PiiRedactorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [PiiRedactorServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['Pii' => Pii::class];
    }
}
