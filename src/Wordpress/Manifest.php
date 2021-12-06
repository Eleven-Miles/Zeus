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
            add_action('init', [$this, 'preload']);
        }

        add_action('enqueue_block_editor_assets', [$this, 'blockEditorAssets']);
        add_action('after_setup_theme', [$this, 'wysiwygEditorAssets']);
        add_action('wp_head', [$this, 'localizeScript']);
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
        if (!is_iterable($this->manifestFiles)) return;

        foreach ($this->manifestFiles as $name => $file) {
            // Skip editor styles from preload
            if (strpos($name, 'editor') !== false || strpos($name, '.map')) continue;

            $filename = get_template_directory_uri() . "/public/dist/$file";

            if (strpos($file, '.js')) {
                wp_enqueue_script($name, $filename, [], null, true);
                header("Link: <$filename>;as=script;rel=preload", false);
            }

            if (strpos($file, '.css')) {
                wp_enqueue_style($name, $filename);
                header("Link: <$filename>;as=style;rel=preload", false);
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
        wp_enqueue_style('block-editor-styles', get_theme_file_uri() . "/public/dist/{$editor_style_file}", false, '1.0', 'all');
        wp_enqueue_script('block-editor-js', get_theme_file_uri() . "/public/dist/{$editor_script_file}");
        add_editor_style(get_theme_file_uri() . "/public/dist/{$editor_style_file}");
    }

    /**
     * This will add styles to the TinyMCE editor in the WP Admin
     */
    public function wysiwygEditorAssets()
    {
        $editor_style_file = $this->manifestFiles['editor.css'];
        add_editor_style(get_theme_file_uri() . "/public/dist/{$editor_style_file}");
    }

    /**
     * Injects variables in head to ensure they will be available to any assets
     * that have been pre-loaded via
     */
    public function localizeScript()
    {
        $object = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce()
        ];
        $script = "var wp_ajax = " . wp_json_encode($object) . ';';
        echo "<script>$script</script>";
    }
}
