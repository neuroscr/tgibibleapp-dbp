<?php

namespace App\Models\Collection;

use Illuminate\Database\Eloquent\Model;

class CollectionPlaylist extends Model
{
    protected $connection = 'dbp_users';
    public $table         = 'collection_playlists';
}
