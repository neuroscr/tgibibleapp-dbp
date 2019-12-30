<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlaylistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('dbp_users')->hasTable('user_playlists')) {
            Schema::connection('dbp_users')->create('user_playlists', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('thumbnail', 191)->nullable()->default(null);
                $table->boolean('featured')->default(false);
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id', 'FK_user_playlists')->references('id')->on(config('database.connections.dbp_users.database') . '.users')->onDelete('cascade')->onUpdate('cascade');
                $table->string('external_content', 200)->default('');
                $table->softDeletes();
                $table->timestamps();
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
        Schema::connection('dbp_users')->dropIfExists('user_playlists');
    }
}
