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
use Pi\Db\RowGateway\RowGateway as BlockRow;
use Zend\View\Model\ViewModel;
use Zend\View\Helper\AbstractHelper;
use MarkdownDocument;

/**
 * Helper for fetching and rendering a block
 *
 * When cache is enabled for a block,
 * its immediate data are cached instead of final rendered content
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->block('block-name',
 *      array('title_hidden' => 1, 'opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->block('block-name',
 *      array('link' => '/link/to/a/URL', 'opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->block('block-name',
 *      array('style' => 'specified-css-class',
 *      'opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->block(24, array('opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->block()->load(24);
 *  $this->block()->render($blockModel);
 * ```
 *
 * @see Pi\Application\Registry\Block
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Block extends AbstractHelper
{
    /**
     * Load a block row from database
     *
     * @param string|int $id
     * @return BlockRow
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
     * @return  self|array|false
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
    public function renderBlock(BlockRow $blockRow, $options = array())
    {
        if (!$blockRow->active) {
            return false;
        }
        $block = $blockRow->toArray();

        // Override with instant options
        foreach (array(
            'title',
            'link',
            'class',
            'cache_ttl',
            'cache_level',
            'template',
            'title_hidden'
        ) as $key) {
            if (isset($options[$key])) {
                $block[$key] = $options[$key];
            }
        }
        if (!empty($block['title_hidden'])) {
            $block['title'] = '';
        }

        $renderCache = null;
        $blockData = null;
        if ('tab' != $block['type'] && $block['cache_ttl']) {
        //if ($block['cache_ttl']) {
            $cacheKey = empty($options)
                ? md5($block['id']) : md5($block['id'] . serialize($options));
            $renderCache = Pi::service('render_cache')->setType('block');
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
            if ($renderCache) {
                $renderCache->saveCache(json_encode($blockData));
            }
        } else {
            if (Pi::service()->hasService('log')) {
                Pi::service('log')->info(
                    sprintf('Block "%s" is cached', $block['name'])
                );
            }
        }


        if ('tab' == $block['type']) {
            $content = $blockData;
        } else {

            $viewModel = new ViewModel;
            // Assemble template
            if (!$block['template']) {
                $template = 'system:block/dummy';
            } else {
                $template = sprintf(
                    '%s:block/%s',
                    $block['module'],
                    $block['template']
                );
                // The block's module
                $viewModel->setVariable('module', $block['module']);
                // Matched route
                $routeMatch = Pi::engine()->application()->getRouteMatch();
                $viewModel->setVariable('route', $routeMatch);
            }
            $viewModel->setTemplate($template)->terminate(true);
            $viewModel->setVariable('block', $blockData);
            $content = $this->view->render($viewModel);
        }
        $block['content'] = $content;

        return $block;
    }

    /**
     * Build the content of the block for output
     *
     * @param BlockRow $blockRow
     * @param array $configs
     * @return array|string Variable array for module blocks
     *      and string content for custom blocks
     */
    public function buildBlock(BlockRow $blockRow, $configs = array())
    {
        $block = $blockRow->toArray();

        // Merge run-time configs with system settings
        $options = isset($block['config']) ? $block['config'] : array();
        if (!empty($configs)) {
            $options = array_merge($options, $configs);
        }

        // Render blocks from `widget` module
        if ('widget' == $block['module']) {
            $result = Pi::api('block', 'widget')->render($this, $block, $options);
        // Render block from regular modules
        } else {
            // Load translations for corresponding module block
            Pi::service('i18n')->loadModule('block', $block['module']);

            // Render contents
            $result = call_user_func_array(
                $block['render'],
                array($options, $block['module'])
            );
        }

        return $result;
    }
}
