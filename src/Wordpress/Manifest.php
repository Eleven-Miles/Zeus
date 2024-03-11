<?php

namespace NanoSoup\Zeus\Wordpress;

use NanoSoup\Zeus\ModuleConfig;

/**
 * Class Manifest
 * @package Zeus\Wordpress
 */
class Manifest
{
    /**
     * @var array $manifestFiles
     */
    private $manifestFiles = [];

    /**
     * Manifest constructor.
     */
    public function __construct($moduleConfig)
    {
        $config = new ModuleConfig($moduleConfig);

        if ($config->getOption('disabled')) {
            return;
        }

        $this->loadManifest();

        if (!is_admin() && $GLOBALS['pagenow'] !== 'wp-login.php') {
            // Priority 20 ensures our styles load after other common plugins i.e. WooCommerce
            // so that we can override more easily
            add_action('wp_enqueue_scripts', [$this, 'preload'], 20);
        }

        add_action('admin_enqueue_scripts', [$this, 'blockEditorAssets']);
    }

    /**
     * Loads asset manifest file array into array property
     */
    public function loadManifest()
    {
        $manifest_path = get_template_directory() . '/public/dist/manifest.json';

        if (file_exists($manifest_path)) {
            $this->manifestFiles = json_decode(file_get_contents($manifest_path), true);
        }
    }

    /**
     * Preload function to load main css & js files from asset manifest file
     */
    public function preload()
    {
        if (!is_iterable($this->manifestFiles)) {
            return;
        }

        $theme_public_path = get_template_directory_uri() . "/public/dist/";

        foreach ($this->manifestFiles as $name => $file) {
            // Skip editor styles from preload
            if (strpos($name, 'editor') !== false || strpos($name, '.map')) {
                continue;
            }

            // Check to see if file contains $theme_public_path, if not add it
            $filename = (strpos($file, $theme_public_path) !== false) ?
                $file :
                $theme_public_path . $file;

            if (strpos($name, 'app.js') !== false) {
                wp_enqueue_script($name, $filename, [], null, true);
                header("Link: <$filename>;as=script;rel=prefetch;crossorigin=anonymous;", false);
            }

            if (strpos($file, '.css') !== false) {
                wp_enqueue_style($name, $filename);
                header("Link: <$filename>;as=style;rel=prefetch;crossorigin=anonymous;", false);
            }

            if (strpos($file, '.woff') !== false || strpos($file, '.ttf') !== false) {
                header("Link: <$filename>;as=font;rel=preload;crossorigin=anonymous;", false);
            }
        }
    }

    /**
     * This will add styles to the block editor in the WP Admin
     */
    public function blockEditorAssets()
    {
        $editor_style_file = $this->manifestFiles['editor.css'];
        $editor_script_file = $this->manifestFiles['editor.js'];

        wp_enqueue_style('block-editor-styles', get_site_url() . $editor_style_file, false, '1.0', 'all');
        wp_enqueue_script('block-editor-js', get_site_url() . $editor_script_file, [], '1.0', true);
        add_editor_style($editor_style_file);
        wp_localize_script('block-editor-js', 'wpAjaxAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'securityToken' => wp_create_nonce('ajax-security'),
        ]);
    }
}
