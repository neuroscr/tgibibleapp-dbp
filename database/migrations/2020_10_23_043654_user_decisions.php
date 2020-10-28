<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserDecisions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('dbp_users')->table('users', function (Blueprint $table) {
            $table->string('decision_name')->after('remember_token')->nullable()->default(null);
            $table->string('decision_date')->after('decision_name')->nullable()->default(null);
            $table->boolean('decision_want_resources')->after('decision_date')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('dbp_users')->table('users', function (Blueprint $table) {
            $table->dropColumn('decision_name');
            $table->dropColumn('decision_date');
            $table->dropColumn('decision_want_resources');
        });
    }
}
