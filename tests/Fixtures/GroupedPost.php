<?php

namespace DavidGut\Sortable\Tests\Fixtures;

use DavidGut\Sortable\Contracts\Sortable;
use DavidGut\Sortable\Traits\HasPosition;
use Illuminate\Database\Eloquent\Model;

class GroupedPost extends Model implements Sortable
{
    use HasPosition;

    protected $guarded = [];

    protected $table = 'grouped_posts';

    protected $positionScope = 'category_id';
}
