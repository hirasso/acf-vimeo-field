<?php

/**
 * ACF Vimeo Field
 *
 * @author            Rasso Hilber
 * @copyright         2025 Rasso Hilber
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: ACF Vimeo Field
 * Description: An ACF field to embed video files from vimeo.com directly on your site 🍿
 * Author: Rasso Hilber
 * Author URI: https://rassohilber.com/
 * Text Domain: acf-vimeo-field
 * Requires PHP: 8.2
 * Version: 0.0.1
 */

namespace Hirasso\ACFVimeoField;

if (is_readable(__DIR__.'/vendor/autoload.php')) {
    require_once __DIR__.'/vendor/autoload.php';
}

$plugin = new Plugin(__FILE__);

add_action('plugins_loaded', [$plugin, 'init']);
