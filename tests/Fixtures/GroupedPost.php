<?php

namespace DavidGut\Sortable\Tests\Fixtures;

use DavidGut\Sortable\Contracts\Sortable;
use DavidGut\Sortable\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;

class GroupedPost extends Model implements Sortable
{
    use SortableTrait;

    protected $guarded = [];

    protected $table = 'grouped_posts';

    protected $sortScope = 'category_id';
}
