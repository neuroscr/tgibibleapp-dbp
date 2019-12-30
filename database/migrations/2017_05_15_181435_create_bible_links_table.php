<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateBibleLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('dbp')->hasTable('bible_links')) {
            Schema::connection('dbp')->create('bible_links', function (Blueprint $table) {
                $table->increments('id');
                $table->string('bible_id', 12);
                $table->foreign('bible_id', 'FK_bibles_bible_links')->references('id')->on(config('database.connections.dbp.database') . '.bibles')->onDelete('cascade')->onUpdate('cascade');
                $table->string('type');
                $table->text('url');
                $table->string('title');
                $table->string('provider')->nullable();
                $table->boolean('visible')->default(1);
                $table->integer('organization_id')->unsigned()->nullable();
                $table->foreign('organization_id', 'FK_organizations_bible_links')->references('id')->on(config('database.connections.dbp.database') . '.organizations');
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
        Schema::connection('dbp')->dropIfExists('bible_links');
    }
}
