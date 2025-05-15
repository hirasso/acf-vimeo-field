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
4. Paste in a Vimeo URL on a page that contains that field group
5. In your template, use `get_field('my_vimeo_field', $post_id)`

<details>
  <summary>The return value will look something like this:</summary>

```php
Hirasso\ACFVimeoField\VimeoVideo {#3498 ▼
  +ID: "1079358564"
  +url: "https://vimeo.com/1079358564"
  +width: 1920
  +height: 1080
  +files: array:6 [▼
    0 =>
Hirasso\ACFVimeoField
\
VimeoVideoFile {#3556 ▼
      +quality: "sd"
      +rendition: "240p"
      +type: "video/mp4"
      +width: 426
      +height: 240
      +link: "
https://player.vimeo.com/progressive_redirect/playback/1079358564/rendition/240p/file.mp4?loc=external&oauth2_token_id=1787645880&signature=db9f5cd429c2c667190a
 ▶
"
      +created_time: "2025-04-28T12:44:50+00:00"
      +fps: 25.0
      +size: 138634
      +size_short: "135.38KB"
      +public_name: "240p"
      +md5: null
    }
    1 =>
Hirasso\ACFVimeoField
\
VimeoVideoFile {#3535 ▼
      +quality: "sd"
      +rendition: "360p"
      +type: "video/mp4"
      +width: 640
      +height: 360
      +link: "
https://player.vimeo.com/progressive_redirect/playback/1079358564/rendition/360p/file.mp4?loc=external&oauth2_token_id=1787645880&signature=0693eb1c7373b99cbe27
 ▶
"
      +created_time: "2025-04-28T12:43:08+00:00"
      +fps: 25.0
      +size: 304155
      +size_short: "297.03KB"
      +public_name: "360p"
      +md5: null
    }
    2 =>
Hirasso\ACFVimeoField
\
VimeoVideoFile {#3541 ▼
      +quality: "sd"
      +rendition: "540p"
      +type: "video/mp4"
      +width: 960
      +height: 540
      +link: "
https://player.vimeo.com/progressive_redirect/playback/1079358564/rendition/540p/file.mp4?loc=external&oauth2_token_id=1787645880&signature=3fc74daa161764bd5856
 ▶
"
      +created_time: "2025-04-28T12:43:04+00:00"
      +fps: 25.0
      +size: 863802
      +size_short: "843.56KB"
      +public_name: "540p"
      +md5: null
    }
    3 =>
Hirasso\ACFVimeoField
\
VimeoVideoFile {#3491 ▼
      +quality: "hd"
      +rendition: "720p"
      +type: "video/mp4"
      +width: 1280
      +height: 720
      +link: "
https://player.vimeo.com/progressive_redirect/playback/1079358564/rendition/720p/file.mp4?loc=external&oauth2_token_id=1787645880&signature=8a0b2d2b992609f3d45f
 ▶
"
      +created_time: "2025-04-28T12:43:03+00:00"
      +fps: 25.0
      +size: 1751107
      +size_short: "1.67MB"
      +public_name: "720p"
      +md5: null
    }
    4 =>
Hirasso\ACFVimeoField
\
VimeoVideoFile {#3517 ▼
      +quality: "hd"
      +rendition: "1080p"
      +type: "video/mp4"
      +width: 1920
      +height: 1080
      +link: "
https://player.vimeo.com/progressive_redirect/playback/1079358564/rendition/1080p/file.mp4?loc=external&oauth2_token_id=1787645880&signature=e90755f3d99266049f8
 ▶
"
      +created_time: "2025-04-28T12:43:16+00:00"
      +fps: 25.0
      +size: 7363718
      +size_short: "7.02MB"
      +public_name: "1080p"
      +md5: null
    }
    5 =>
Hirasso\ACFVimeoField
\
VimeoVideoFile {#3515 ▼
      +quality: "hls"
      +rendition: "adaptive"
      +type: "video/mp4"
      +width: null
      +height: null
      +link: "https://player.vimeo.com/external/1079358564.m3u8?s=1f1eb96738516e177b5836e43f386d5d0ba58298&oauth2_token_id=1787645880"
      +created_time: "2025-04-28T12:43:08+00:00"
      +fps: 25.0
      +size: 304155
      +size_short: "297.03KB"
      +public_name: "360p"
      +md5: null
    }
  ]
}
```
</details>