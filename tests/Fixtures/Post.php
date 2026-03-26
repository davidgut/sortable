<?php

namespace DavidGut\Sortable\Tests\Fixtures;

use DavidGut\Sortable\Contracts\Sortable;
use DavidGut\Sortable\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements Sortable
{
    use SortableTrait;

    protected $guarded = [];

    protected $table = 'posts';
}
