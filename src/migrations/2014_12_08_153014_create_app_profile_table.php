<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppProfileTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'app_profile',
            function ($table) {
                $table->bigIncrements('id');
                $table->text('url');
                $table->double('total_duration', 15, 5);
                $table->double('app_logic_duration', 15, 5);
                $table->double('db_queries_duration', 15, 5);
                $table->smallInteger('db_queries_count');
                $table->double('memory_usage', 15, 5);
                $table->smallInteger('included_files_count');
                $table->timestamp('run_date');
                $table->longText('log');
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
        Schema::drop('app_profile');
    }
}
