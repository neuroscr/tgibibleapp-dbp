<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBibleFilesStreamBytesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('dbp')->hasTable('bible_file_stream_bytes')) {
            Schema::connection('dbp')->create('bible_file_stream_bytes', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('stream_bandwidth_id')->unsigned();
                $table->foreign('stream_bandwidth_id', 'FK_bible_file_bandwidth_stream_bytes')->references('id')->on(config('database.connections.dbp.database') . '.bible_file_stream_bandwidths')->onUpdate('cascade')->onDelete('cascade');
                $table->float('runtime');
                $table->integer('bytes');
                $table->integer('offset');
                $table->integer('timestamp_id')->unsigned();
                $table->foreign('timestamp_id', 'FK_bible_file_timestamp_stream_bytes')->references('id')->on(config('database.connections.dbp.database') . '.bible_file_timestamps')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::connection('dbp')->dropIfExists('bible_file_stream_bytes');
    }
}
