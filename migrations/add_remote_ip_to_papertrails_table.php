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
        Schema::connection(config('papertrail.database_connection'))->table(config('papertrail.table_name'), function (Blueprint $table) {
            $table->string('remote_ip')->default('0.0.0.0')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('papertrail.database_connection'))->table(config('papertrail.table_name'), function (Blueprint $table) {
            $table->dropColumn('remote_ip');
        });
    }
}
