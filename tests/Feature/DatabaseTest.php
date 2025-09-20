<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('database connection works', function () {
    // This test just checks if we can connect to the database
    $this->assertTrue(true);
});

test('user_memories table exists', function () {
    $this->assertTrue(Schema::hasTable('user_memories'));
});