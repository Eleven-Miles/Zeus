<?php

namespace NanoSoup\Zeus\Wordpress;

use NanoSoup\Zeus\ModuleConfig;

/**
 * Class Yoast
 * @package Zeus\Wordpress
 */
class Yoast
{
    /**
     * Yoast constructor.
     */
    public function __construct($moduleConfig)
    {
        $config = new ModuleConfig($moduleConfig);

        if ($config->getOption('disabled')) {
            return;
        }

        if ($config->getOption('disableAdminFilters')) {
            add_action('admin_init', [$this, 'removeYoastAdminFilters'], 20);
        }

        if ($config->getOption('disableOptimisations')) {
            add_filter('wpseo_metabox_prio', [$this, 'yoastToBottom']);
            add_filter('wpseo_canonical', [$this, 'wpseoRemovePageUrl']);
            add_filter('wpseo_breadcrumb_single_link_info', [$this, 'shortenYoastBreadcrumbTitle'], 10);
            add_filter('robots_txt', [$this, 'addSitemapToRobots'], 10, 1);
        }

        add_theme_support('yoast-seo-breadcrumbs');
    }

    /**
     *
     */
    public function removeYoastAdminFilters()
    {
        global $wpseo_meta_columns;
        if ($wpseo_meta_columns) {
            remove_action('restrict_manage_posts', [$wpseo_meta_columns, 'posts_filter_dropdown']);
            remove_action('restrict_manage_posts', [$wpseo_meta_columns, 'posts_filter_dropdown_readability']);
        }
    }

    /**
     * Moves the Yoast scripts to the bottom of the page
     *
     * @return string
     */
    public function yoastToBottom()
    {
        return 'low';
    }

    /**
     * Remove pagination from canonical URLs
     * @param $link
     * @return null|string|string[]
     */
    public function wpseoRemovePageUrl($link)
    {
        $link = preg_replace('~page/(0|[1-9][0-9]*)/?~i', '', $link);

        return $link;
    }

    /**
     * @param $link_info
     * @return mixed
     */
    public function shortenYoastBreadcrumbTitle($link_info)
    {
        $limit = 30;
        if (strlen($link_info['text']) > ($limit)) {
            $link_info['text'] = substr($link_info['text'], 0, $limit) . '&hellip;';
        }

        return $link_info;
    }

    /**
     * Add site map to robots.txt
     * @param $output
     * @return string
     */
    public function addSitemapToRobots($output)
    {
        $output .= 'Sitemap: ' . get_site_url() . '/sitemap_index.xml';
        return $output;
    }
}
