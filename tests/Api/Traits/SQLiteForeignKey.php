<?php

namespace Tests\Api\Traits;

use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;

trait SQLiteForeignKey
{
    private function activateForeignKeysOption(): void
    {
        if (DB::connection() instanceof SQLiteConnection) {
            DB::statement(DB::raw('PRAGMA foreign_keys=1'));
        }
    }
}