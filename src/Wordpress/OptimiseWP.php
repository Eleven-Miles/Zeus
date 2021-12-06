<?php

namespace NanoSoup\Zeus\Wordpress;

use NanoSoup\Zeus\ModuleConfig;

/**
 * Class OptimiseWP
 * @package Zeus\Wordpress
 */
class OptimiseWP
{
    /**
     * OptimiseWP constructor.
     */
    public function __construct($moduleConfig)
    {
        $config = new ModuleConfig($moduleConfig);

        if ($config->getOption('disabled')) {
            return;
        }

        if ($config->getOption('disableJquery')) {
            add_action('wp_enqueue_scripts', [$this, 'removeJqueryScript']);
            add_action('wp_default_scripts', [$this, 'removeJquery']);
        }

        if ($config->getOption('disableScripts')) {
            add_action('wp_enqueue_scripts', [$this, 'removeUnused']);
        }

        if ($config->getOption('diableEmojis')) {
            add_action('init', [$this, 'disableEmojis']);
        }

        if ($config->getOption('disableHeadLinks')) {
            remove_action('wp_head', 'wp_generator');
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'wlwmanifest_link');
            remove_action('wp_head', 'wp_shortlink_wp_head');
        }

        if ($config->getOption('disableRestAPI')) {
            add_filter('rest_endpoints', [$this, 'disableRestApi']);
        }

        if ($config->getOption('disableAdminBar')) {
            $user = wp_get_current_user();
            $roles = [
                'administrator',
                'report_manager',
                'stock_manager',
                'account_manager',
                'seo_manager',
                'seo_editor',
                'editor',
                'author',
                'contributor',
            ];

            if (count(array_intersect($roles, $user->roles)) <= 0) {
                add_filter('show_admin_bar', '__return_false');
            }
        }

        add_action('login_enqueue_scripts', [$this, 'adminLogo']);
        add_filter('xmlrpc_enabled', '__return_false');
    }

    /**
     *
     */
    public function adminLogo()
    {
?>
        <style>
            #login h1 a,
            .login h1 a {
                background-image: url(<?= get_stylesheet_directory_uri(); ?>/public/dist/svgs/logo.svg);
                height: 65px;
                width: 320px;
                background-size: 320px 65px;
                background-repeat: no-repeat;
                padding-bottom: 30px;
            }
        </style>
<?php
    }

    /**
     *
     */
    public function removeUnusedScripts()
    {
        wp_deregister_script('wp-embed');
        add_filter('the_generator', [$this, 'removeVersion']);
        add_filter('style_loader_src', [$this, 'sdtRemoveVerCssJs'], 9999);
        add_filter('script_loader_src', [$this, 'sdtRemoveVerCssJs'], 9999);
    }

    /**
     *
     */
    public function removeJqueryScript()
    {
        wp_dequeue_script('jquery');
    }

    /**
     *
     * if ($config->getOption('disableRestAPI')) {
     * public function disableRestApi($endpoints)}
     * @param $endpoints
     * @return mixed
     */

    public function disableRestApi($endpoints)
    {
        if (isset($endpoints['/wp/v2/users'])) {
            unset($endpoints['/wp/v2/users']);
        }

        if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
        }

        return $endpoints;
    }

    /**
     * @param $urls
     * @param $relation_type
     * @return array
     */
    public function disableEmojisRemoveDnsPrefetch($urls, $relation_type)
    {
        if ('dns-prefetch' == $relation_type) {
            /** This filter is documented in wp-includes/formatting.php */
            $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');

            $urls = array_diff($urls, [$emoji_svg_url]);
        }

        return $urls;
    }

    /**
     * @return string
     */
    public function removeVersion()
    {
        return '';
    }

    /**
     * @param $plugins
     * @return array
     */
    public function disableEmojisTinymce($plugins)
    {
        if (is_array($plugins)) {
            return array_diff($plugins, ['wpemoji']);
        } else {
            return [];
        }
    }

    /**
     *
     */
    public function disableEmojis()
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        add_filter('tiny_mce_plugins', [$this, 'disableEmojisTinymce']);
        add_filter('wp_resource_hints', [$this, 'disableEmojisRemoveDnsPrefetch'], 10, 2);
    }

    /**
     * @param $src
     * @return string
     */
    public function sdtRemoveVerCssJs($src)
    {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }

        return $src;
    }

    /**
     * @param $scripts
     */
    public function removeJquery($scripts)
    {
        if (!is_admin() && isset($scripts->registered['jquery'])) {
            $script = $scripts->registered['jquery'];

            if ($script->deps) { // Check whether the script has any dependencies
                $script->deps = array_diff($script->deps, ['jquery-migrate']);
            }
        }
    }
}
