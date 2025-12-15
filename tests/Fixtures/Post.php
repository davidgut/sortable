<?php

namespace DavidGut\Sortable\Tests\Fixtures;

use DavidGut\Sortable\Contracts\Sortable;
use DavidGut\Sortable\Traits\HasPosition;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements Sortable
{
    use HasPosition;

    protected $guarded = [];

    protected $table = 'posts';
}
