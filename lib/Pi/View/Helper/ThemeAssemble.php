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
     * Load meta from configuration
     *
     * @return void
     */
    public function initStrategy()
    {
        // Load meta config
        $configMeta = Pi::config('', 'system', 'meta');
        // Set head meta
        foreach ($configMeta as $key => $value) {
            if (!$value) {
                continue;
            }
            $this->view->headMeta()->appendName($key, $value);
        }

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
        $configGeneral['locale'] = Pi::service('i18n')->locale
            ?: $configGeneral['locale'];
        $configGeneral['charset'] = Pi::service('i18n')->charset
            ?: $configGeneral['charset'];
        $this->view->plugin('view_model')->getRoot()
            ->setVariables($configGeneral);

        // Initialize headTitle helper
        $headTitle = $this->view->headTitle();
        // Set separator
        $separator = $headTitle->setSeparator(' - ');
    }

    /**
     * Canonize head title by appending site name and/or slogan
     *
     * @return void
     */
    public function renderStrategy()
    {
        $headTitle      = $this->view->headTitle();
        $separator      = $headTitle->getSeparator();
        $currentModule  = Pi::service('module')->current();

        // Set slogan as page title for homepage
        if ((!$currentModule || 'system' == $currentModule)
            && !$headTitle->count()
        ) {
            $headTitle->set(Pi::config('slogan'));
        }

        // Set postfix
        $postfix = $headTitle->getPostfix();
        if (!$postfix) {
            $postfix = Pi::config('sitename');
            if ($currentModule && 'system' != $currentModule) {
                $moduleMeta = Pi::registry('module')->read($currentModule);
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
