<?php

namespace App\Models\Collection;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Collection extends Model
{
    protected $connection = 'dbp_users';
    public $table         = 'collections';
    //

    public function user()
    {
        return $this->belongsTo(User::class)->select('id', 'name');
    }
}
