<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ColllectionImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add field
        Schema::connection('dbp_users')->table('collections', function (Blueprint $table) {
            $table->string('thumbnail_url')
                ->after('language_id')
                ->nullable()
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // remove field
        Schema::connection('dbp_users')->table('collections', function (Blueprint $table) {
            $table->dropColumn('thumbnail_url'); // use country_maps style
        });
    }
}
