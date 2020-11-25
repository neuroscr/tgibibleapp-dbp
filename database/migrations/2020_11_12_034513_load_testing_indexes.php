<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LoadTestingIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('dbp_users')->hasTable('users')) {
            Schema::connection('dbp_users')->table('users', function (Blueprint $table) {
                // should this be unique?
                $table->index('token');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::connection('dbp_users')->hasTable('users')) {
            Schema::connection('dbp_users')->table('users', function (Blueprint $table) {
                $table->dropIndex(['token']);
            });
        }
    }
}
