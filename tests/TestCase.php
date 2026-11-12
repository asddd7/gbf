<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUpTraits()
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        $this->app['db']->setDefaultConnection('sqlite');
        $this->app['db']->purge('sqlite');

        return parent::setUpTraits();
    }
}
