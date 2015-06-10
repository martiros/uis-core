<?php

use Illuminate\Database\Migrations\Migration;

class CreateDictionaryNdkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'dictionary_ndk',
            function ($table) {
                $table->bigIncrements('id');
                $table->string('hash')->unique();
                $table->string('key');
                $table->string('app_name');
                $table->string('module');
                $table->text('url');
                $table->text('from_url');
                $table->timestamp('add_date');
                $table->string('ip');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dictionary_ndk');
    }
}
