<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('dbp_users')->hasTable('user_downloads')) {
            Schema::connection('dbp_users')->create('user_downloads', function (Blueprint $table) {
                $table->increments('id');
                // user_id
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id', 'FK_users_user_api_tokens')->references('id')->on(config('database.connections.dbp_users.database') . '.users')->onUpdate('cascade');

                // bible_id
                // book_id
                // chapter _id
                // drama
                $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        //
    }
}
