<?php
/**
 * Block helper
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
use Pi\Db\RowGateway\RowGateway as BlockRow;
use Pi\Mvc\CacheEvent;
use Pi\Security;
use Zend\View\Model\ViewModel;
use Zend\View\Helper\AbstractHelper;
use MarkdownDocument;
//use Pi\Markup\Markup;

/**
 * Helper for fetching and rendering a block
 * @see Pi\Application\Registry\Block
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->block('block-name', array('title_hidden' => 1, 'opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->block('block-name', array('link' => '/link/to/a/URL', 'opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->block('block-name', array('style' => 'specified-css-class', 'opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->block(24, array('opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->block()->load(24);
 *  $this->block()->render($blockModel);
 * </code>
 */
class Block extends AbstractHelper
{
    /**
     * Context specific CacheEvent
     * @var /Pi/Mvc/CacheEvent
     */
    protected static $cacheEvent;

    /**
     * Get CacheEvent for block content cache
     *
     * NOTE: currently block intermediate data are cached instead of rendered content, thus the method is not used yet
     *
     * @return type
     */
    protected function cacheEvent()
    {
        if (!$this->cacheEvent) {
            $this->cacheEvent = Pi::engine()->loadResource('cache')->cacheEvent('block');
        }
        return $this->cacheEvent;
    }

    /**
     * Load a block model from database
     *
     * @param string|int $id
     * @return  BlockModel
     */
    public function load($id)
    {
        $block = null;
        $model = Pi::model('block');
        if (is_numeric($id)) {
            $block = $model->find($id);
        } elseif (is_string($id)) {
            $rowset = $model->select(array('name' => $id));
            $block = $rowset->current();
        }

        return $block;
    }

    /**
     * Render a block
     *
     * @param   string|int|BlockRow $block
     * @param   array $options
     * @return  Block|array|BlockModel
     */
    public function __invoke($block = null, $options = array())
    {
        if (null === $block) {
            return $this;
        }
        if (!$block instanceof BlockRow) {
            $block = $this->load($block);
        }
        if (!$block instanceof BlockRow) {
            return false;
        }

        return $this->render($block, $options);
    }

    /**
     * Render a block
     *
     * @param   BlockRow $blockRow
     * @param   array $options
     * @return  array
     */
    public function render(BlockRow $blockRow, $options = array())
    {
        return $this->renderBlock($blockRow, $options);
    }

    /**
     * Render a block
     *
     * @param   BlockRow $blockRow
     * @param   array $options
     * @return  array
     */
    protected function renderBlock(BlockRow $blockRow, $options = array())
    {
        if (!$blockRow->active) {
            return false;
        }
        $block = $blockRow->toArray();

        // Override with instant options
        foreach (array('title', 'link', 'class', 'cache_ttl', 'cache_level', 'template', 'title_hidden') as $key) {
            if (isset($options[$key])) {
                $block[$key] = $options[$key];
            }
        }
        if (!empty($block['title_hidden'])) {
            $block['title'] = '';
        }

        $renderCache = null;
        //$cacheOptions = null;
        $blockData = null;
        if ('tab' != $block['type'] && $block['cache_ttl']) {
            $cacheKey = empty($options) ? md5($block['id']) : md5($block['id'] . serialize($options));
            /*
            $cacheKey = 'b' . $cacheKey;
            $cacheOptions = array(
                'ttl'       => $block['cache_ttl'],
                'namespace' => $block['module'] ?: 'system',
            );
            $blockData = Pi::service('cache')->getItem($cacheKey, $cacheOptions);
            */
            $renderCache = Pi::service('render')->setType('block');
            $renderCache->meta('key', $cacheKey)
                    ->meta('namespace', $block['module'] ?: 'system')
                    ->meta('ttl', $block['cache_ttl']);
            $blockData = $renderCache->cachedContent();
            if (null !== $blockData) {
                $blockData = json_decode($blockData, true);
            }
        }
        if (null === $blockData) {

            // Profiling
            Pi::service('log')->start('BLOCK: ' . $blockRow->title);

            $blockData = $this->buildBlock($blockRow, $options);

            // Profiling
            Pi::service('log')->end('BLOCK: ' . $blockRow->title);

            if (false === $blockData) {
                return false;
            }
            /*
            if ($cacheOptions) {
                Pi::service('cache')->setItem($cacheKey, json_encode($blockData), $cacheOptions);
            }
            */
            if ($renderCache) {
                $renderCache->saveCache(json_encode($blockData));
            }
        } else {
            if (Pi::service()->hasService('log')) {
                Pi::service('log')->info(sprintf('Block "%s" is cached', $block['name']));
            }
        }

        if ('tab' == $block['type']) {
            $content = $blockData;
        } else {
            $viewModel = new ViewModel;
            // Assemble template
            if (!$block['template']) {
                $template = 'module/system:block/dummy';
            } else {
                $template = sprintf('module/%s:block/%s', $block['module'], $block['template']);
                /**#@+
                    * Preset variables
                    */
                // The block's module
                $viewModel->setVariable('module', $block['module']);
                // Matched route
                $routeMatch = Pi::engine()->application()->getRouteMatch();
                $viewModel->setVariable('route', $routeMatch);
                /**#@-*/
            }
            $viewModel->setTemplate($template)->terminate(true);
            $viewModel->setVariable('block', $blockData);
            $content = $this->view->render($viewModel);
        }
        $block['content'] = $content;

        return $block;
    }

    /**
     * return the content of the block for output
     *
     * @param BlockRow $blockRow
     * @return array|string Variable array for module blocks and string content for custom blocks
     */
    public function buildBlock(BlockRow $blockRow, $configs = array())
    {
        $result = false;
        $block = $blockRow->toArray();
        $isCustom = $block['type'] ? true : false;
        // Module-generated block, return array
        if (!empty($block['render'])) {
            Pi::service('i18n')->loadModule('block', $block['module']);
            $options = isset($block['config']) ? $block['config'] : array();
            if (!empty($configs)) {
                $options = array_merge($options, $configs);
            }
            $result = call_user_func_array($block['render'], array($options, $block['module']));
        // Custom block, return string
        } elseif ($isCustom) {
            switch ($block['type']) {
                // carousel
                case 'carousel':
                    $items = empty($block['content']) ? false : json_decode($block['content'], true);
                    if ($items) {
                        $result = array(
                            'items'     => $items,
                            'options'   => $configs,
                        );
                    }
                    break;
                // compound tab
                case 'tab':
                    $result = $this->transliterateTabs($block['content']);
                    break;
                // static HTML
                case 'html':
                    $result = Pi::service('markup')->render($block['content'], 'html');
                    $result = $this->transliterateGlobals($result);
                    break;
                // static mardown
                case 'markdown':
                    $result = Pi::service('markup')->render($block['content'], 'html', 'markdown');
                    $result = $this->transliterateGlobals($result);
                    break;
                // static text
                case 'text':
                default:
                    $result = Pi::service('markup')->render($block['content'], 'text');
                    $result = $this->transliterateGlobals($result);
                    break;
            }
        }

        return $result;
    }

    /**
     * Transliterate compound into tabs
     *
     * @param string $content
     * @return array
     */
    protected function transliterateTabs($content)
    {
        $result = array();
        $list = json_decode($content, true);

        // Build blocks content
        foreach ($list as $tab) {
            $entity = isset($tab['name']) ? $tab['name'] : intval($tab['id']);
            $row = $this->load($entity);
            if (!$row || !$row->active) {
                continue;
            }
            $data = $this->renderBlock($row);
            if (empty($data['content'])) {
                continue;
            }
            $result[] = array(
                'caption'   => !empty($tab['caption']) ? $tab['caption'] : $data['title'],
                'link'      => !empty($tab['link']) ? $tab['link'] : '',
                'content'   => $data['content'],
            );
        }

        return $result;
    }


    /**
     * Transliterate global variables, allowed tags: %sitename%, %slogan%, %siteurl%
     *
     * @param string $content
     * @return string
     */
    protected function transliterateGlobals($content)
    {
        $globalsMap = array(
            'sitename'  => Pi::config('sitename'),
            'slogan'    => Pi::config('slogan'),
            'siteurl'   => Pi::url('www'),
        );
        foreach ($globalsMap as $var => $val) {
            $content = str_replace('%' . $var . '%', $val, $content);
        }

        return $content;
    }
}
