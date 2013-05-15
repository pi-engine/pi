<?php
/**
 * Page rendering assemble helper
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Renderer\RendererInterface as Renderer;

/**
 * Helper for rendering assemble
 *
 * Usage inside a theme phtml template:
 * <code>
 *  $this->assemble('footScript', 4);
 * </code>
 */
class Assemble extends AbstractHelper
{
    /**
     * Section labels
     *
     * @var array
     */
    protected $sectionLabel;

    /**
     * Render a section's anchor
     *
     * @param   string  $section
     * @param   string|int $indent
     * @return  string|RenderSection
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
        $label = '<' . $section . ' id="' . md5(Pi::config('salt') . $section) . '" />';
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
        $configMeta = Pi::service('registry')->config->read('system', 'meta');
        // Set head meta
        foreach ($configMeta as $key => $value) {
            if (!$value) {
                continue;
            }
            $this->view->headMeta()->appendName($key, $value);
        }

        // Load general config
        $configGeneral = Pi::service('registry')->config->read('system');

        // Set Google Analytics scripts in case available
        if ($configGeneral['ga_account']) {
            $this->view->footScript()->appendScript($this->view->ga($configGeneral['ga_account']));
        }
        // Set foot scripts in case available
        if ($configGeneral['foot_script']) {
            if (false !== stripos($configGeneral['foot_script'], '<script ')) {
                $this->view->footScript()->appendScript($configGeneral['foot_script'], 'raw');
            } else {
                $this->view->footScript()->appendScript($configGeneral['foot_script']);
            }
        }
        unset($configGeneral['ga_account'], $configGeneral['foot_script']);

        // Set global variables to root ViewModel, e.g. theme template
        $this->view->plugin('view_model')->getRoot()->setVariables($configGeneral);
    }

    /**
     * Canonize head title by appending site name and/or slogan
     *
     * @return void
     */
    public function renderStrategy()
    {
        $headTitle = $this->view->headTitle();
        $hasCustom = $headTitle->count();
        $headTitle->setSeparator(' - ');

        // Append module name for non-system module
        $currentModule = Pi::service('module')->current();
        if ($currentModule && 'system' != $currentModule) {
            $moduleMeta = Pi::service('registry')->module->read($currentModule);
            $headTitle->append($moduleMeta['title']);
        }
        // Append site name
        $headTitle->append(Pi::config('sitename'));

        // Append site slogan if no custom title available
        if (!$hasCustom) {
            $headTitle->append(Pi::config('slogan'));
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
        //$indent = 4;
        $head = '';
        //$headTitle = '';
        /*
        $headTitle = $this->view->headTitle()->toString() . PHP_EOL;
        if (!empty($this->sectionLabel['headTitle'])) {
            $content = str_replace($this->sectionLabel['headTitle'], $headTitle, $content);
        } else {
            $head .= $headTitle . PHP_EOL;
        }
        */

        foreach (array('headTitle', 'headMeta', 'headLink', 'headStyle', 'headScript') as $section) {
            $sectionContent = $this->view->plugin($section)->toString();
            $sectionContent .= $sectionContent ? PHP_EOL : '';
            if (!empty($this->sectionLabel[$section])) {
                $content = str_replace($this->sectionLabel[$section], $sectionContent, $content);
            } else {
                $head .= $sectionContent . PHP_EOL;
            }
        }

        if ($head) {
            $pos = stripos($content, '</head>');
            $preHead = substr($content, 0, $pos);
            $postHead = substr($content, $pos);
            $content = $preHead . PHP_EOL . $head . PHP_EOL . $postHead;
        }
        /**#@-*/

        /**@+
         * Generates and inserts foot scripts
         */
        $section = 'footScript';
        $sectionContent = $this->view->plugin($section)->toString();
        if (!empty($this->sectionLabel[$section])) {
            $content = str_replace($this->sectionLabel[$section], $sectionContent, $content);
        } elseif ($sectionContent) {
            $pos = stripos($content, '</body>');
            $preFoot = substr($content, 0, $pos);
            $postFoot = substr($content, $pos);
            $content = $preFoot . PHP_EOL . $sectionContent . PHP_EOL . PHP_EOL . $postFoot;
        }
        /**#@-*/

        return $content;
    }
}