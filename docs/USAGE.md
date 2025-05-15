# Usage

1. Get a vimeo access token with `private`, `video_files` and `public` permissions.
2. Store the access token it in your `.env` file and define it in your `wp-config.php`:

```shell
# in your .env file:
VIMEO_ACCESS_TOKEN="01239df23..."
```

```php
// in your wp-config.php file:
define('VIMEO_ACCESS_TOKEN', env('VIMEO_ACCESS_TOKEN') ?? '');
```

3. Add a field with type 'Vimeo Video' to one of your field groups
4. Paste in a vimeo URL that you own