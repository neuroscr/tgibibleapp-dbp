<?php

namespace Tests\Integration;

use App\Models\Bible\BibleFile;

class PlaylistRoutesTest extends ApiV4Test
{

    /**
     * @category V4_API
     * @category Route Name: v4_media_stream
     * @category Route Path: https://api.dbp.test/?v=4&key={key}/stream/{file_id}/playlist.m3u8
     * @see      VideoStreamController::index
     * @group    V4
     * @group    travis
     * @test
     */
    public function videoStream()
    {
        $path = route('v4_media_stream', array_merge($this->params, [
            'file_id'    => $bible_file->id,
            'fileset_id' => $bible_file->fileset->id,
            'file_name'  => $bible_file->file_name
        ]));

        //$this->markTestIncomplete('Travis not handling files');
        /*
        $bible_file = BibleFile::with('fileset')->where('file_name', 'like', '%.m3u8')->inRandomOrder()->first();
        $path = route('v4_media_stream', array_merge($this->params, [
            'file_id'    => $bible_file->id,
            'fileset_id' => $bible_file->fileset->id,
            'file_name'  => $bible_file->file_name
        ]));

        $response = $this->withHeaders($this->params)->get($path);
        $response->assertSuccessful();

        $disposition_header = $response->headers->get('content-disposition');
        $this->assertContains('attachment', $disposition_header);
        $this->assertContains('filename="' . $bible_file->file_name . '"', $disposition_header);
        */
    }
}
