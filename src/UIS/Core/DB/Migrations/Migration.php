<?php

namespace UIS\Core\DB\Migrations;

use Illuminate\Database\Migrations\Migration as IlluminateMigration;
use UIS\Core\DB\Schema\Blueprint;
use DB;

class Migration extends IlluminateMigration
{
    public function getDB()
    {
        return DB::connection();
    }

    public function getSchemaBuilder()
    {
        $schema = DB::connection()->getSchemaBuilder();
        $schema->blueprintResolver(function($table, $callback) {
            return new Blueprint($table, $callback);
        });
        return $schema;
    }
}
