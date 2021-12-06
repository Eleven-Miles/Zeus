<?php

namespace NanoSoup\Zeus\Wordpress;

use NanoSoup\Zeus\ModuleConfig;

/**
 * Class Dashboard
 * @package Zeus\Wordpress
 */
class Dashboard
{
    /**
     * Dashboard constructor.
     */
    public function __construct($moduleConfig)
    {
        $config = new ModuleConfig($moduleConfig);

        if ($config->getOption('disabled')) {
            return;
        }

        if ($config->getOption('disableWidgets')) {
            add_action('wp_dashboard_setup', [$this, 'disableDefaultDashboardWidgets'], 999);
            add_action('current_screen', [$this, 'disableDragMetabox']);
            remove_action('welcome_panel', 'wp_welcome_panel');
        }

        if ($config->getOption('disableComments')) {
            add_action('admin_menu', [$this, 'removeAdminCommentsMenu']);
            add_action('init', [$this, 'removeCommentSupport'], 100);
            add_action('wp_before_admin_bar_render', [$this, 'adminBarRender']);
        }

        if ($config->getOption('removeAdminColumns')) {
            add_filter('manage_edit-post_columns', [$this, 'removeColumnsFromAdmin']);
            add_filter('manage_edit-page_columns', [$this, 'removeColumnsFromAdmin']);
            add_filter('manage_edit-brand_columns', [$this, 'removeColumnsFromAdmin']);
            add_filter('manage_media_columns', [$this, 'removeColumnsFromAdmin']);
        }

        add_action('after_setup_theme', [$this, 'themeSupport']);
        add_action('after_setup_theme', [$this, 'registerNavMenus']);
        add_action('customize_register', [$this, 'removeItemsFromCustomizer'], 15);

        remove_action('wp_before_admin_bar_render', 'wp_customize_support_script');
    }

    /**
     *
     */
    public function disableDefaultDashboardWidgets()
    {
        global $wp_meta_boxes;
        unset($wp_meta_boxes['dashboard']['normal']);
        unset($wp_meta_boxes['dashboard']['side']);
    }

    /**
     * Disable meta box dragging ( tis a silly thing )
     */
    public function disableDragMetabox()
    {
        if (is_admin()) {
            $screen = get_current_screen();
            if ($screen->id === 'dashboard') {
                // This is the admin Dashboard screen
                wp_deregister_script('postbox');
            }
        }
    }

    /**
     * Remove comment management in admin
     */
    public function removeAdminCommentsMenu()
    {
        remove_menu_page('edit-comments.php');
    }

    /**
     * Remove comment support
     */
    public function removeCommentSupport()
    {
        $post_types = get_post_types();
        foreach ($post_types as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }

    /**
     * Remove items from admin bar
     */
    public function adminBarRender()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_menu('wpseo-menu');
        $wp_admin_bar->remove_menu('new-post');
    }

    /**
     * Declares theme support for different WP elements
     */
    public function themeSupport()
    {
        add_theme_support('html5', ['search-form']);
        add_theme_support('automatic-feed-links');
        add_theme_support('menus');
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');
    }

    /**
     * This will register navigation menus from the `nav_menu` taxonomy
     */
    public function registerNavMenus()
    {
        $menus = get_terms(['taxonomy' => 'nav_menu']);

        foreach ($menus as $menu) {
            register_nav_menu($menu->slug, $menu->name);
        }
    }

    /**
     * Remove unwanted columns from admin
     * @param $columns
     * @return mixed
     */
    public function removeColumnsFromAdmin($columns)
    {
        unset($columns['wpseo-linked']);
        unset($columns['wpseo-links']);
        unset($columns['wpseo-score']);
        unset($columns['wpseo-score-readability']);
        unset($columns['wpseo-title']);
        unset($columns['wpseo-metadesc']);
        unset($columns['wpseo-focuskw']);
        unset($columns['smushit']);

        return $columns;
    }

    /**
     * @param $wp_customize
     */
    public function removeItemsFromCustomizer($wp_customize)
    {
        $wp_customize->remove_section('custom_css');
    }
}
