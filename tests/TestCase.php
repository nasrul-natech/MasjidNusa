<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // Validate before Laravel initializes RefreshDatabase or any other test trait.
        $connection = (string) $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || ! in_array($database, [':memory:', ''], true)) {
            throw new \RuntimeException(
                'Tes dibatalkan agar database aplikasi tidak terhapus ('.$connection.'/'.$database.').'
            );
        }

        return $app;
    }
}
