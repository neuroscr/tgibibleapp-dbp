<?php

namespace App\Models\Collection;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $connection = 'dbp_users';
    public $table         = 'collections';
    //
}
