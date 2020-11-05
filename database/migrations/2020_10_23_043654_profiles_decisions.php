<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProfilesDecisions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('dbp_users')->table('profiles', function (Blueprint $table) {
            $table->string('decision_name')->after('phone')->nullable()->default(null);
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
        Schema::connection('dbp_users')->table('profiles', function (Blueprint $table) {
            $table->dropColumn('decision_name');
            $table->dropColumn('decision_date');
            $table->dropColumn('decision_want_resources');
        });
    }
}
