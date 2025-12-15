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
       // protected $positionScope = 'category_id'; // Sorts uniquely per category
   }
   ```

3. **Add the API Route.**
   Add the following to your `routes/web.php` or `routes/api.php` to handle updates.

   ```php
   use DavidGut\Sortable\Http\Controllers\PositionController;

   Route::put('/sortable/{model}/{id}', PositionController::class)->name('sortable.update');
   ```

## Frontend Usage

This package includes a 0-config wrapper for [SortableJS](https://sortablejs.github.io/Sortable/).

1. **Install SortableJS.**
   ```bash
   npm install sortablejs
   ```

2. **Publish the assets.**
   This copies the JS wrapper to your resources folder.
   ```bash
   php artisan vendor:publish --tag=sortable-assets
   ```

3. **Import and Start.**
   In your `app.js`:

   ```javascript
   import SortableList from './vendor/sortable/sortable';

   SortableList.start();
   ```

4. **Add `data-sortable` to your list.**
   The library will automatically find lists with this attribute.
   
   - `data-sortable`: Marks the container.
   - `data-sortable-update-url`: The endpoint to hit when dropped.
   - `.drag`: (Optional) Use this class on an element to make it the drag handle.

   ```html
   <ul data-sortable>
       @foreach($posts as $post)
           <li data-sortable-update-url="{{ route('sortable.update', ['model' => 'Post', 'id' => $post->id]) }}">
               <span class="drag">:::</span>
               {{ $post->title }}
           </li>
       @endforeach
   </ul>
   ```

   > **Note:** Ensure you have the `<meta name="csrf-token">` tag in your layout head so requests don't fail.

## Configuration (Optional)

You can publish the config file to register model aliases (useful if you don't want to expose full class names in URLs).

```bash
php artisan vendor:publish --tag=sortable-config
```

Then in `config/sortable.php`:

```php
'models' => [
    'posts' => \App\Models\Post::class,
],
```

Now your route can be `/sortable/posts/1` instead of `/sortable/Post/1`.

## Security

By default, only users where `$user->isAdmin()` returns true can resort items. You can override this in your model:

```php
public function canBePositionedBy($user): bool
{
    return $user->id === $this->user_id;
}
```
