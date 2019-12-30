<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateBibleOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('dbp')->hasTable('bible_organizations')) {
            Schema::connection('dbp')->create('bible_organizations', function ($table) {
                $table->string('bible_id', 12)->nullable();
                $table->foreign('bible_id', 'FK_bibles_bible_organizations')->references('id')->on(config('database.connections.dbp.database') . '.bibles')->onDelete('cascade')->onUpdate('cascade');
                $table->integer('organization_id')->unsigned()->nullable();
                $table->foreign('organization_id', 'FK_organizations_bible_organizations')->references('id')->on(config('database.connections.dbp.database') . '.organizations');
                $table->string('relationship_type');
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
        Schema::connection('dbp')->dropIfExists('bible_organizations');
    }
}
