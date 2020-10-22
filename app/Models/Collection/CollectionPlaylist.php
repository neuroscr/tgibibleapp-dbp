<?php

namespace App\Models\Collection;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema (
 *     type="object",
 *     description="The Collection Playlists data",
 *     title="CollectionPlaylist"
 * )
 */
class CollectionPlaylist extends Model
{
    protected $connection = 'dbp_users';
    public $table         = 'collection_playlists';
}
