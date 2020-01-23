<?php

namespace Zeus\Wordpress;

/**
 * Class ACF
 * @package Zeus\Wordpress
 */
class ACF
{
    /**
     * ACF constructor.
     */
    public function __construct()
    {
        //add_filter('acf/settings/show_admin', '__return_false');
        add_action('after_setup_theme', [$this, 'setupOptionsPage']);
        add_action('acf/init', [$this, 'registerGoogleMapsKey']);
        add_filter('allowed_block_types', [$this,  'allowedBlocks']);

        // add action for logged-in users
        add_action( "wp_ajax_acf/ajax/check_screen", [$this,  'allowedBlocks2'], 1);

        add_action( "wp_ajax_nopriv_acf/ajax/check_screen", [$this,  'allowedBlocks2'], 1);

        add_filter('block_categories', [$this, 'registerCustomBlockCats'], 10, 1);
    }

    /**
     *
     */
    public function setupOptionsPage()
    {
        if (!function_exists('acf_add_options_page')) {
            return;
        }

        if (!current_user_can('administrator')) {
            return;
        }

        acf_add_options_page([
            'page_title' => 'Site Settings',
            'menu_title' => 'Site Settings',
            'menu_slug' => 'site-settings',
            'capability' => 'manage_options',
        ]);
    }

    /**
     *
     */
    public function registerGoogleMapsKey()
    {
        if (defined('GOOGLE_MAP_API') && !empty(GOOGLE_MAP_API)) {
            acf_update_setting('google_api_key', GOOGLE_MAP_API);
        }
    }

    /**
     * To prevent duplication of cats you need to add the "Custom" block cats
     * once then assign your blocks to them
     *
     * @param $categories
     * @return array
     */
    public function registerCustomBlockCats($categories)
    {
        return array_merge(
            $categories,
            [
                [
                    'slug' => 'homepage',
                    'title' => 'Homepage Blocks'
                ],
                [
                    'slug' => 'content',
                    'title' => 'Content Blocks'
                ],
                [
                    'slug' => 'media',
                    'title' => 'Media Blocks'
                ]
            ]
        );
    }

    /**
     * This will limit the core blocks in Gutenberg and allow your custom ones
     *
     * @param $allowed_block_types
     * @return array
     */
    public function allowedBlocks($allowed_block_types)
    {
        $blocks = acf_get_block_types();

        $allowed = [
            'gravityforms/block'
        ];

        foreach ($blocks as $block) {
            if (in_array(get_post_type(), $block['post_types'])) {
                $allowed[] = $block['name'];
            }
        }

        return $allowed;
    }
}