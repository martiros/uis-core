<?php

use UIS\Core\DB\Schema\Blueprint;
use UIS\Core\DB\Migrations\Migration;
use UIS\Core\Models\BaseModel;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schemaBuilder = $this->getSchemaBuilder();
        if ($schemaBuilder->hasTable('language')) {
            return;
        }

        $schemaBuilder->create('language', function(Blueprint $table){
            $table->smallInteger('id', true, true);
            $table->string('code', 3);
            $table->string('name');
            $table->string('en_name');
            $table->smallInteger('sort_order')->unsigned();
            $table->enum('is_default', [BaseModel::FALSE, BaseModel::TRUE]);
            $table->showStatus('show_status', false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $schemaBuilder = $this->getSchemaBuilder();
        if (!$schemaBuilder->hasTable('language')){
            return;
        }
        $schemaBuilder->drop('language');
    }
}
