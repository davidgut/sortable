# Laravel Sortable

Drag-and-drop sorting for your Eloquent models — backend and frontend included.  
No dependencies, no build step required for the JS, just install and go.

Supports Laravel 11, 12 & 13.

## Quick Start

```bash
composer require davidgut/sortable
```

Add a `position` column to any table you want to sort:

```php
$table->integer('position')->nullable();
```

Then implement the contract on your model:

```php
use DavidGut\Sortable\Contracts\Sortable;
use DavidGut\Sortable\Traits\HasPosition;

class Post extends Model implements Sortable
{
    use HasPosition;
}
```

That's it for the backend. New records automatically get the next position, and the package registers a `PUT /sortable/{model}/{id}` route for you.

### Frontend

Publish the included JavaScript:

```bash
php artisan vendor:publish --tag=sortable-assets
```

Import it in your `app.js`:

```javascript
import SortableList from './vendor/sortable/sortable';

SortableList.start();
```

Mark up your list:

```html
<ul data-sortable>
    @foreach($posts as $post)
        <li @sortableUrl($post)>
            <span class="drag">:::</span>
            {{ $post->title }}
        </li>
    @endforeach
</ul>
```

> The `@sortableUrl` directive outputs the full `data-sortable-update-url="..."` attribute.  
> You can also write it manually if you prefer: `data-sortable-update-url="{{ $post->sortableUrl() }}"`.


Make sure you have a `<meta name="csrf-token">` tag in your layout — the JS reads it for requests.

Done. Your list is now sortable.

---

## Configuration

### Registering Models

Publish the config:

```bash
php artisan vendor:publish --tag=sortable-config
```

Map your models in `config/sortable.php`:

```php
'models' => [
    'posts' => \App\Models\Post::class,
],
```

> In non-production environments the package will also try to resolve `App\Models\{Name}` automatically, so you can skip this step during development. In production, only explicitly registered models are allowed.

### Custom Position Column

The default column is `position`. To change it, set the property on your model:

```php
class Post extends Model implements Sortable
{
    use HasPosition;

    protected $positionColumn = 'sort_order';
}
```

### Scoped Sorting

Need separate sort orders per group? For example, sorting posts within each category independently:

```php
class Post extends Model implements Sortable
{
    use HasPosition;

    protected ?string $positionScope = 'category_id';
}
```

Each `category_id` will now have its own position sequence starting from 0.

### Custom Position Queries

For more advanced cases, override `getPositionQuery()`:

```php
protected function getPositionQuery(): Builder
{
    return parent::getPositionQuery()->where('is_active', true);
}
```

### Custom Routes

The package auto-registers routes with `web` middleware. If you need to customise them:

```bash
php artisan vendor:publish --tag=sortable-routes
```

### Query Scope

Retrieve records in sorted order:

```php
Post::sorted()->get();
Post::sorted('desc')->get();
```

---

## Authorization

By default, only users where `$user->isAdmin()` returns `true` can re-sort items. Override this per model:

```php
public function canBeSortedBy($user): bool
{
    return $user->id === $this->user_id;
}
```

---

## SPA / Livewire

`SortableList.start()` returns the created instances. Call `SortableList.stop()` to tear them down on navigation:

```javascript
const instances = SortableList.start();

// Later, on page leave:
SortableList.stop();
```

---

## License

MIT
