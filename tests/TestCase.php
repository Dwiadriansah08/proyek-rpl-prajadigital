<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment with strict safety checks.
     */
    protected function setUp(): void
    {
        if (! $this->app) {
            $this->refreshApplication();
        }

        $this->ensureSafeTestDatabase();

        parent::setUp();
    }

    /**
     * Enforce strict isolation so tests NEVER touch the development MySQL 'rpl' database.
     *
     * @throws RuntimeException
     */
    protected function ensureSafeTestDatabase(): void
    {
        $defaultConnection = config('database.default');
        $mysqlDatabase = config('database.connections.mysql.database');
        $activeConfigDatabase = config("database.connections.{$defaultConnection}.database");

        // Rule 1: The default connection MUST be sqlite, never mysql
        if ($defaultConnection !== 'sqlite') {
            throw new RuntimeException(
                "CRITICAL SAFETY VIOLATION: Test connection is '{$defaultConnection}', expected 'sqlite'. Halting to protect database."
            );
        }

        // Rule 2: The database MUST be in-memory (:memory:)
        if ($activeConfigDatabase !== ':memory:') {
            throw new RuntimeException(
                "CRITICAL SAFETY VIOLATION: Test SQLite database is configured as '{$activeConfigDatabase}', expected ':memory:'. Halting."
            );
        }

        // Rule 3: Guard against development database name 'rpl'
        if (strcasecmp((string) $mysqlDatabase, 'rpl') === 0 && $defaultConnection === 'mysql') {
            throw new RuntimeException(
                "CRITICAL SAFETY VIOLATION: Tests attempted to run against MySQL development database 'rpl'! Execution aborted."
            );
        }

        // Rule 4: Neutralize MySQL connection settings in test container memory to prevent accidental calls
        config([
            'database.connections.mysql.database' => 'safety_guarded_test_disabled',
            'database.connections.mysql.host' => '127.0.0.1_disabled_in_tests',
        ]);

        // Rule 5: Verify runtime PDO connection does NOT resolve to development database
        try {
            $currentDb = DB::connection()->getDatabaseName();
            if ($currentDb !== ':memory:' && (strcasecmp((string) $currentDb, 'rpl') === 0 || str_contains(strtolower((string) $currentDb), 'rpl'))) {
                throw new RuntimeException(
                    "CRITICAL SAFETY VIOLATION: Active database connection resolved to '{$currentDb}'. Halting to protect development data."
                );
            }
        } catch (\Throwable $e) {
            if ($e instanceof RuntimeException && str_starts_with($e->getMessage(), 'CRITICAL SAFETY VIOLATION')) {
                throw $e;
            }
            // If connection has not opened yet, this is expected before first query
        }
    }
}
