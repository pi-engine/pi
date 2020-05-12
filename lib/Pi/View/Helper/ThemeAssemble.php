<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Laminas\View\Helper\AbstractHelper;

/**
 * Helper for page element rendering assemble in theme
 *
 * Usage inside a theme phtml template:
 *
 * ```
 *  $this->assemble('footScript', 4);
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ThemeAssemble extends AbstractHelper
{
    /**
     * Section labels
     * @var array
     */
    protected $sectionLabel;

    /**
     * Render a section's anchor
     *
     * @param   string $section
     * @param   string|int $indent
     * @return  string|self
     */
    public function __invoke($section = null, $indent = null)
    {
        if (!$section) {
            return $this;
        }
        $helper = $this->view->plugin($section);
        if (null !== $indent) {
            $helper->setIndent($indent);
        }
        $label                        = sprintf(
            '<%s id="%s" />',
            $section,
            md5(Pi::config('salt') . $section)
        );
        $this->sectionLabel[$section] = $label;

        return $label;
    }

    /**
     * Load basic meta from configuration
     *
     * @return void
     */
    public function initStrategy()
    {
        // Initialize headTitle helper
        $headTitle = $this->view->headTitle();
        // Set separator
        $headTitle->setSeparator(' - ');

        // Load general config
        $configGeneral = Pi::config('', 'system', 'general');

        // Set foot scripts in case available
        if ($configGeneral['foot_script']) {
            if (false !== stripos($configGeneral['foot_script'], '<script ')) {
                $this->view->footScript()->appendScript(
                    $configGeneral['foot_script'],
                    'raw'
                );
            } else {
                $this->view->footScript()->appendScript(
                    $configGeneral['foot_script']
                );
            }
        }
        unset($configGeneral['foot_script']);

        // Set global variables to root ViewModel, e.g. theme template
        $configGeneral['locale']  = Pi::service('i18n')->getLocale()
            ?: $configGeneral['locale'];
        $configGeneral['charset'] = Pi::service('i18n')->getCharset()
            ?: $configGeneral['charset'];
        $this->view->plugin('view_model')->getRoot()
            ->setVariables($configGeneral);
    }

    /**
     * Load head meta from configuration
     *
     * @param string $module
     *
     * @return void
     */
    public function bootStrategy($module)
    {
        $headMeta = $this->view->headMeta();
        // Load meta config
        $configMeta = Pi::config('', 'system', 'head_meta');
        $moduleMeta = [];
        unset($configMeta['head_title']);
        if ('system' != $module) {
            $moduleMeta = Pi::config('', $module, 'head_meta');
            if (isset($moduleMeta['head_title'])) {
                unset($moduleMeta['head_title']);
            }
        }

        // Set head meta
        foreach ($configMeta as $key => $value) {
            $meta = empty($moduleMeta[$key]) ? $value : $moduleMeta[$key];
            if (!$meta) {
                continue;
            }
            $headMeta->appendName($key, $meta);
        }

        // Set shear image
        $image = Pi::service('asset')->socialNetworkLogo();

        // Get informations
        $sitename      = Pi::config('sitename');
        $slogan        = Pi::config('slogan');
        $description   = Pi::config('description');
        $locale        = Pi::service('i18n')->getLocale();
        $ogLocale      = Pi::config('og_local');
        $twitter       = Pi::config('twitter_account');
        $facebook      = Pi::config('facebook_appid');
        $pinterest     = Pi::config('pinterest_verify');
        $geoLatitude   = Pi::config('geo_latitude');
        $geoLongitudet = Pi::config('geo_longitude');
        $geoPlacename  = Pi::config('geo_placename');
        $geoRegion     = Pi::config('geo_region');

        // Meta author and generator
        $headMeta($sitename, 'author');
        $headMeta($sitename, 'generator');

        // Open Graph
        $headMeta($sitename, 'og:title', 'property');
        $headMeta($sitename, 'og:site_name', 'property');
        $headMeta($description, 'og:description', 'property');
        $headMeta(Pi::url(), 'og:url', 'property');
        $headMeta($ogLocale, 'og:locale', 'property');
        $headMeta('website', 'og:type', 'property');
        $headMeta($image, 'og:image', 'property');

        // Twitter Cards
        $headMeta('summary', 'twitter:card');
        $headMeta($twitter, 'twitter:site');
        $headMeta($twitter, 'twitter:creator');
        $headMeta($sitename, 'twitter:title');
        $headMeta($description, 'twitter:description');
        $headMeta($image, 'twitter:image');
        $headMeta(Pi::url(), 'twitter:url');

        // Facebook
        if (!empty($facebook)) {
            $headMeta($facebook, 'fb:app_id', 'property');
        }

        // Pinterest
        if (!empty($pinterest)) {
            $headMeta($pinterest, 'p:domain_verify');
        }

        // Geo tags
        if (!empty($geoLatitude) && !empty($geoLongitudet)) {
            $this->view->geoTag($geoLatitude, $geoLongitudet, $geoPlacename, $geoRegion);
        }
    }

    /**
     * Canonize head title by appending site name and/or slogan
     *
     * @param string $module
     *
     * @return void
     */
    public function renderStrategy($module)
    {
        $headTitle = $this->view->headTitle();
        $separator = $headTitle->getSeparator();
        $postfix   = $headTitle->getPostfix();
        $prefix    = $headTitle->getPrefix();
        $type      = Pi::config('title_type');
        $sitename  = Pi::config('sitename');

        // Set prefix or postfix
        switch ($type) {
            case 1:
                if (!$postfix) {
                    $postfix = $sitename;
                    if ($headTitle->count()) {
                        if ($module && 'system' != $module) {
                            $moduleMeta = Pi::registry('module')->read($module);
                            $postfix    = $moduleMeta['title'] . $separator . $postfix;
                        }
                    }
                    $postfix = $separator . $postfix;
                    $headTitle->setPostfix($postfix);
                }
                break;

            case 2:
                if (!$postfix) {
                    $postfix = $sitename;
                    $postfix = $separator . $postfix;
                    $headTitle->setPostfix($postfix);
                }
                break;

            case 3:
                if (!$prefix) {
                    $prefix = $sitename;
                    if ($headTitle->count()) {
                        if ($module && 'system' != $module) {
                            $moduleMeta = Pi::registry('module')->read($module);
                            $prefix     = $prefix . $separator . $moduleMeta['title'];
                        }
                    }
                    $prefix = $prefix . $separator;
                    $headTitle->setPrefix($prefix);
                }
                break;

            case 4:
                if (!$prefix) {
                    $prefix = $sitename;
                    $prefix = $prefix . $separator;
                    $headTitle->setPrefix($prefix);
                }
                break;

            case 5:
                break;
        }

        // Set head title
        if (!$headTitle->count()) {
            $headTitleStr = Pi::config('head_title', $module);
            if (empty($headTitleStr) && $module && 'system' != $module) {
                $moduleMeta   = Pi::registry('module')->read($module);
                $headTitleStr = $moduleMeta['title'];
            }
            if ($headTitleStr && $headTitleStr == $sitename) {
                $headTitle->setPrefix('');
                $headTitle->setPostfix('');
            }
            if (!empty($slogan)) {
                $slogan = $separator . $slogan;
                $headTitle->setPostfix($slogan);
            }
            $headTitle->set($headTitleStr);
        }
    }

    /**
     * Complete assembling meta contents
     *
     * @param string $content
     * @return string
     */
    public function completeStrategy($content)
    {
        /**#@+
         * Generates and inserts head meta, stylesheets and scripts
         */
        $pos = stripos($content, '</head>');
        if ($pos) {
            $head = '';
            foreach ([
                         'headTitle',
                         'headMeta',
                         'headStyleCritical',
                         'headLink',
                         'headStyle',
                         'headScript',
                     ] as $section) {
                $sectionContent = $this->view->plugin($section)->toString();
                $sectionContent .= $sectionContent ? PHP_EOL : '';
                if (!empty($this->sectionLabel[$section])) {
                    $content = str_replace(
                        $this->sectionLabel[$section],
                        $sectionContent,
                        $content
                    );
                } else {
                    $head .= $sectionContent . PHP_EOL;
                }
            }

            if ($head) {
                $preHead  = substr($content, 0, $pos);
                $postHead = substr($content, $pos);
                $content  = $preHead . PHP_EOL . $head . PHP_EOL . $postHead;
            }
        }
        /**#@-*/

        /**@+
         * Generates and inserts foot scripts
         */
        $pos = stripos($content, '</body>');
        if ($pos) {
            $section        = 'footScript';
            $sectionContent = $this->view->plugin($section)->toString();
            if (!empty($this->sectionLabel[$section])) {
                $content = str_replace(
                    $this->sectionLabel[$section],
                    $sectionContent,
                    $content
                );
            } elseif ($sectionContent) {
                $preFoot  = substr($content, 0, $pos);
                $postFoot = substr($content, $pos);
                $content  = $preFoot . PHP_EOL . $sectionContent . PHP_EOL . PHP_EOL
                    . $postFoot;
            }
        }
        /**#@-*/

        return $content;
    }
}