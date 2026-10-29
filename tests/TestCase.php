<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || ! in_array($database, [':memory:', ''], true)) {
            throw new \RuntimeException(
                'Tes dibatalkan agar database aplikasi tidak terhapus ('.$connection.'/'.$database.').'
            );
        }
    }
}
