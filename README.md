# Laravel Sortable

Simple drag-and-drop sorting for your Laravel models.

## Installation

```bash
composer require davidgut/sortable
```

## Setup

1. **Add the column to your table.**
   By default, the package looks for a `position` column.

   ```php
   Schema::create('posts', function (Blueprint $table) {
       // ...
       $table->integer('position')->nullable();
   });
   ```

2. **Implement the Contract and Trait in your Model.**

   ```php
   use DavidGut\Sortable\Contracts\Sortable;
   use DavidGut\Sortable\Traits\HasPosition;

   class Post extends Model implements Sortable
   {
       use HasPosition;

       // Optional configuration
       // protected $positionColumn = 'custom_order';
       // protected ?string $positionScope = 'category_id'; // Sorts uniquely per category
   }
   ```

3. **Register your models in the config.**
   Publish the config file:

   ```bash
   php artisan vendor:publish --tag=sortable-config
   ```

   Then map your models in `config/sortable.php`:

   ```php
   'models' => [
       'posts' => \App\Models\Post::class,
   ],
   ```

   > **Note:** In non-production environments, the package will also try to resolve `App\Models\{name}` automatically so you can get started without config. In production, only explicitly registered models are allowed.

The package automatically registers a `PUT /sortable/{model}/{id}` route named `sortable.update` with `web` middleware. To customise it, publish the route file:

   ```bash
   php artisan vendor:publish --tag=sortable-routes
   ```

## Frontend Usage

This package includes a lightweight, native JavaScript drag-and-drop implementation. No external dependencies required.

1. **Publish the assets.**
   ```bash
   php artisan vendor:publish --tag=sortable-assets
   ```

2. **Import and Start.**
   In your `app.js`:

   ```javascript
   import SortableList from './vendor/sortable/sortable';

   SortableList.start();
   ```

3. **Add `data-sortable` to your list.**
   The library will automatically find lists with this attribute.
   
   - `data-sortable`: Marks the container.
   - `data-sortable-update-url`: The endpoint to hit when dropped.
   - `data-sortable-handle`: (Optional) CSS selector for the drag handle. Defaults to `.drag`.

   ```html
   <ul data-sortable>
       @foreach($posts as $post)
           <li data-sortable-update-url="{{ route('sortable.update', ['model' => 'posts', 'id' => $post->id]) }}">
               <span class="drag">:::</span>
               {{ $post->title }}
           </li>
       @endforeach
   </ul>
   ```

   > **Note:** Ensure you have the `<meta name="csrf-token">` tag in your layout head so requests don't fail.

### SPA / Livewire Support

`SortableList.start()` returns the created instances, and `SortableList.stop()` tears them down:

```javascript
// Initialize
const instances = SortableList.start();

// Teardown (e.g. on page navigation)
SortableList.stop();
```

## Security

By default, only users where `$user->isAdmin()` returns true can resort items. You can override this in your model:

```php
public function canBeSortedBy($user): bool
{
    return $user->id === $this->user_id;
}
```
