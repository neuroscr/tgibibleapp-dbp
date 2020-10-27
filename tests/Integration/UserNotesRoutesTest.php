<?php

namespace Tests\Integration;

use App\Models\User\AccessGroup;
use App\Models\User\Key;
use App\Models\User\PasswordReset;
use App\Models\User\ProjectMember;
use App\Models\User\ProjectOauthProvider;
use App\Models\User\User;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;

class UserNotesRoutesTest extends ApiV4Test
{
    use WithFaker;

    /**
     * @category V4_API
     * @category Route Name: v4_notes.index
     * @category Route Path: https://api.dbp.test/users/5/notes?v=4&key={key}
     * @see      \App\Http\Controllers\User\NotesController
     * @group    V4
     * @group    travis
     * @test
     */
    public function notesIndex()
    {
        $key = Key::where('key', $this->key)->first();
        $path = route('v4_notes.index', Arr::add($this->params, 'user_id', 451869));
        echo "\nTesting: GET $path";
        $response = $this->withHeaders($this->params)->get($path);
        $response->assertSuccessful();
        $result = json_decode($response->getContent());
        $notes = $result->data;
        $this->assertEquals(15, count($notes));
        // transformer test / relationship test
        $this->assertEquals("Romans", $notes[0]->book_name);
        // getBibleNameAttribute test
        $this->assertEquals("English Standard Version", $notes[0]->bible_name);
        // getVerseTextAttribute test
        $this->assertEquals("For the wages of sin is death, but the free gift of God is eternal life in Christ Jesus our Lord.", $notes[0]->verse_text);
    }

    /**
     * @category V4_API
     * @category Route Name: v4_notes.index
     * @category Route Path: https://api.dbp.test/users/5/notes?v=4&key={key}
     * @see      \App\Http\Controllers\User\NotesController
     * @group    V4
     * @group    travis
     * @test
     */
    public function notesIndexSearch()
    {
        $key = Key::where('key', $this->key)->first();
        $params = array_merge(['user_id' => 451869, 'query'=>'Romans'], $this->params);
        $path = route('v4_notes.index', $params);
        echo "\nTesting: GET $path";
        $response = $this->withHeaders($this->params)->get($path);
        $response->assertSuccessful();
        $result = json_decode($response->getContent());
        $notes = $result->data;
        $this->assertEquals(1, count($notes));
        // transformer test / relationship test
        $this->assertEquals("Romans", $notes[0]->book_name);
        // getBibleNameAttribute test
        $this->assertEquals("English Standard Version", $notes[0]->bible_name);
        // getVerseTextAttribute test
        $this->assertEquals("For the wages of sin is death, but the free gift of God is eternal life in Christ Jesus our Lord.", $notes[0]->verse_text);
    }

    /**
     * @category V4_API
     * @category Route Name: v4_notes.index
     * @category Route Path: https://api.dbp.test/users/5/notes?v=4&key={key}
     * @see      \App\Http\Controllers\User\NotesController
     * @group    V4
     * @group    travis
     * @test
     */
    public function notesIndexSearchEmpty()
    {
        $key = Key::where('key', $this->key)->first();
        $params = array_merge(['user_id' => 451869, 'query'=>'X'], $this->params);
        $path = route('v4_notes.index', $params);
        echo "\nTesting: GET $path";
        $response = $this->withHeaders($this->params)->get($path);
        $response->assertSuccessful();
        $result = json_decode($response->getContent());
        $notes = $result->data;
        $this->assertEquals(0, count($notes));
    }

    /**
     * @category V4_API
     * @category Route Name: v4_notes
     * @category Route Path: https://api.dbp.test/users/5/notes?v=4&key={key}
     * @see      \App\Http\Controllers\User\NotesController
     * @group    V4
     * @group    travis
     * @test
     */
    public function notes()
    {
        $key = Key::where('key', $this->key)->first();
        $test_note = [
            'user_id' => $key->user_id,
            'bible_id' => 'ENGESV',
            'book_id' => 'GEN',
            'chapter' => 1,
            'verse_start' => 1,
            'verse_end' => 2,
            'notes' => 'A generated test note',
        ];
        $path = route('v4_notes.store', $this->params);
        echo "\nTesting: $path";
        $response = $this->withHeaders($this->params)->post($path, $test_note);
        $response->assertSuccessful();

        $test_created_note = json_decode($response->getContent())->data;

        $path = route('v4_notes.show', array_merge(['user_id' => $key->user_id,'note_id' => $test_created_note->id], $this->params));
        echo "\nTesting: $path";
        $response = $this->withHeaders($this->params)->get($path);
        $response->assertSuccessful();

        $path = route('v4_notes.update', array_merge(['user_id' => $key->user_id,'note_id' => $test_created_note->id], $this->params));
        echo "\nTesting: $path";
        $response = $this->withHeaders($this->params)->put($path, ['description' => 'A generated test note that has been updated']);
        $response->assertSuccessful();

        $path = route('v4_notes.destroy', array_merge(['user_id' => $key->user_id,'note_id' => $test_created_note->id], $this->params));
        echo "\nTesting: $path";
        $response = $this->withHeaders($this->params)->delete($path);
        $response->assertSuccessful();
    }
}
