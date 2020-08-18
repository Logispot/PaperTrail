<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePapertrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('papertrail.database_connection'))->create(config('papertrail.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description')->index();
            $table->string('reference_type');
            $table->integer('reference_id');
            $table->string('user_type')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('key');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();

            $table->index(['reference_id', 'reference_type'], 'papertrail');
            $table->index(['user_id', 'user_type'], 'user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('papertrail.database_connection'))->dropIfExists(config('papertrail.table_name'));
    }
}
