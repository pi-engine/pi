<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

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
     * @param   string  $section
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
        $label = sprintf(
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
        $configGeneral['locale'] = Pi::service('i18n')->getLocale()
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
        $moduleMeta = array();
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

        // Dublin Core
        $sitename = Pi::config('sitename');
        $slogan = Pi::config('slogan');
        $locale = Pi::service('i18n')->getLocale();
        $description = Pi::config('description');
        $headMeta($sitename, 'dc:title', 'property', array('lang' => $locale));
        $headMeta($slogan, 'dc:subject', 'property', array('lang' => $locale));
        $headMeta($description, 'dc:description', 'property', array('lang' => $locale));
        $headMeta('text', 'dc:type', 'property');
        $headMeta($sitename, 'dc:publisher', 'property');
        $headMeta($locale, 'dc:language', 'property');
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

        // Head title for system module
        if ('system' == $module) {
            if (!$headTitle->count()) {
                $headTitle->set(Pi::config('sitename'));
                $headTitle->setPostfix($separator . Pi::config('slogan'));
            }
        }
        // Set head title
        if (!$headTitle->count()) {
            $headTitleStr = Pi::config('head_title', $module);
            if ($headTitleStr) {
                $headTitle->set($headTitleStr);
            }
        }

        // Set postfix
        $postfix = $headTitle->getPostfix();
        if (!$postfix) {
            $postfix = Pi::config('sitename');
            if ($module) {
                $moduleMeta = Pi::registry('module')->read($module);
                $postfix = $moduleMeta['title'] . $separator . $postfix;
            }
            if ($headTitle->count()) {
                $postfix = $separator . $postfix;
            }
            $headTitle->setPostfix($postfix);
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
            foreach (array(
                'headTitle',
                'headMeta',
                'headLink',
                'headStyle',
                'headScript'
            ) as $section) {
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
                $preHead = substr($content, 0, $pos);
                $postHead = substr($content, $pos);
                $content = $preHead . PHP_EOL . $head . PHP_EOL . $postHead;
            }
        }
        /**#@-*/

        /**@+
         * Generates and inserts foot scripts
         */
        $pos = stripos($content, '</body>');
        if ($pos) {
            $section = 'footScript';
            $sectionContent = $this->view->plugin($section)->toString();
            if (!empty($this->sectionLabel[$section])) {
                $content = str_replace(
                    $this->sectionLabel[$section],
                    $sectionContent,
                    $content
                );
            } elseif ($sectionContent) {
                $preFoot = substr($content, 0, $pos);
                $postFoot = substr($content, $pos);
                $content = $preFoot . PHP_EOL . $sectionContent . PHP_EOL . PHP_EOL
                         . $postFoot;
            }
        }
        /**#@-*/

        return $content;
    }
}
