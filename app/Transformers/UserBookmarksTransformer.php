<?php

namespace App\Transformers;

use App\Models\User\Study\Bookmark;
use League\Fractal\TransformerAbstract;
use GuzzleHttp\Client;

class UserBookmarksTransformer extends TransformerAbstract
{
    /**
     * @OA\Schema (
     *        type="object",
     *        schema="v4_user_bookmarks",
     *        description="The transformed user bookmarks",
     *        title="v4_user_bookmarks",
     *      @OA\Xml(name="v4_user_bookmarks"),
     *      allOf={
     *        @OA\Schema(ref="#/components/schemas/pagination.alternate"),
     *      },
     *   @OA\Property(property="data", type="array",
     *      @OA\Items(
     *          @OA\Property(property="id",             type="integer"),
     *          @OA\Property(property="bible_id",       ref="#/components/schemas/Bible/properties/id"),
     *          @OA\Property(property="book_id",        ref="#/components/schemas/Book/properties/id"),
     *          @OA\Property(property="book_name",              ref="#/components/schemas/BibleBook/properties/name"),
     *          @OA\Property(property="chapter",        ref="#/components/schemas/BibleFile/properties/chapter_start"),
     *          @OA\Property(property="verse",          ref="#/components/schemas/BibleFile/properties/verse_start"),
     *          @OA\Property(property="verse_text",          ref="#/components/schemas/BibleFile/properties/verse_text"),
     *          @OA\Property(property="created_at",     ref="#/components/schemas/Bookmark/properties/created_at"),
     *          @OA\Property(property="updated_at",     ref="#/components/schemas/Bookmark/properties/updated_at")
     *        )
     *    )
     *   )
     *)
     *
     * @param Bookmark $bookmark
     * @return array
     */
    public function transform(Bookmark $bookmark)
    {
        // no book relationship?
        if (!isset($bookmark->book->name)) {
            // likely remote content set up
            $book_name = '';
            $content_config = config('services.content');
            if (!empty($content_config['url'])) {
                // we can pull book name from content server
                $book_name = cacheRemember('book_name_data',
                  [$bookmark->bible_id, $bookmark->book_id], now()->addDay(),
                  function () use ($bookmark, $content_config) {
                    $client = new Client();
                    $res = $client->get($content_config['url'] . 'bibles/' .
                       $bookmark->bible_id . '/book/' . $bookmark->book_id .
                       '?v=4&key=' . $content_config['key']);
                    $result = json_decode($res->getBody() . '', true);
                    if ($result && $result['data'] && count($result['data'])) {
                      return $result['data'][0]['name'];
                    }
                });
            }
        } else {
             // can use relationship to get content locally
             $book_name = optional($bookmark->book)->name;
        }
        return [
          'id' => (int) $bookmark->id,
          'bible_id' => (string) $bookmark->bible_id,
          'book_id' => (string) $bookmark->book_id,
          'book_name' => (string) $book_name,
          'chapter' => (int) $bookmark->chapter,
          'verse' => (int) $bookmark->verse_start,
          'verse_text' => (string) $bookmark->verse_text,
          'created_at' => (string) $bookmark->created_at,
          'updated_at' => (string) $bookmark->updated_at,
          'tags' => $bookmark->tags
        ];
    }
}
