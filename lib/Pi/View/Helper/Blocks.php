<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace   Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for fetching and rendering blocks on a page
 *
 * Usage
 *
 * - In general PHP file
 *
 * ```
 *  $blocksHelper = $this->plugin('blocks')->setEvent($mvcEvent);
 *  $leftBlocks = $blocksHelper('left');
 *  $rightBlcoks = $blocksHelper->load('right');
 * ```
 *
 * - In PHP template layout.phtml
 *
 * ```
 *      echo '<div class="block-left">';
 *      foreach ($this->blocks('left') as $block) {
 *          echo '<div id="block-' . $block['id'] . '">';
 *          if (!empty($block['title'])) {
 *              if (empty($block['link'])) {
 *                  echo '<div class="block-title">'
 *                      . $block['title'] . '</div>';
 *              } else {
 *                  echo '<div class="block-title"><a href=" . $block['link']
 *                      . '" title="Go to linked page">' . $block['title']
 *                      . '</a></div>';
 *              }
 *          }
 *          echo '<div class="block-content">' . $block['content'] . '</div>';
 *          echo '</div>';
 *      }
 *      echo '</div>';
 * ```
 *
 * @see Pi\Application\Registry\Block
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Blocks extends AbstractHelper
{
    /**
     * Zone map: zone index in database =>
     * zone in layout; 0 for head zone and 99 for foot zone
     * @var array
     */
    protected $zoneMap = array(
        0   => '0',
        1   => '1',
        2   => '2',
        3   => '3',
        4   => '4',
        5   => '5',
        6   => '6',
        7   => '7',
        8   => '8',
        99  => '99',
    );

    /**
     * Loaded blocks
     * @var array
     */
    protected $blocks;

    /**
     * Load blocks of a specified zone
     *
     * @param string|null $zone
     * @return array
     */
    public function __invoke($zone = null)
    {
        /*
        if (null === $zone) {
            return $this;
        }
        */
        return $this->load($zone);
    }

    /**
     * Build layout block contents
     *
     * @param string|null $zone
     * @return array    associative array of blocks
     */
    public function load($zone = null)
    {
        if (null === $this->blocks) {
            // Profiling
            Pi::service('log')->start('BLOCKS');

            $layoutBlocks = array();

            // Read block IDs
            $route      = Pi::engine()->application()->getRouteMatch();
            $module     = $route->getParam('module');
            $controller = $route->getParam('controller');
            $action     = $route->getparam('action');
            $info = Pi::registry('block')->read($module);

            $blocks = array();
            if (isset($info[sprintf('%s-%s-%s',
                $module, $controller, $action)])) {
                $blocks = $info[sprintf('%s-%s-%s',
                                        $module, $controller, $action)];
            } elseif (isset($info[sprintf('%s-%s', $module, $controller)])) {
                $blocks = $info[sprintf('%s-%s', $module, $controller)];
            } elseif (isset($info[$module])) {
                $blocks = $info[$module];
            }

            $blockIds = array();
            foreach ($blocks as $zoneKey => $zoneBlockIds) {
                $blockIds = array_merge($blockIds, $zoneBlockIds);
            }

            $layoutBlocks = array();
            // Load blocks from database
            if (!empty($blockIds)) {
                $blockIds = array_unique($blockIds);
                $modelBlock = Pi::model('block');
                $select = $modelBlock->select()->where(
                    array('id' => $blockIds)
                );
                $result = $modelBlock->selectWith($select);
                $blockRows = array();
                foreach ($result as $row) {
                    $blockRows[$row->id] = $row;
                }
                $blockHelper = $this->view->block();
                foreach ($blocks as $zoneKey => $zoneBlocks) {
                    foreach ($zoneBlocks as $id) {
                        // Render block
                        $widget = $blockHelper->render($blockRows[$id]);
                        if ($widget) {
                            $layoutBlocks[$this->zoneMap[$zoneKey]][] =
                                $widget;
                        }
                    }
                }
            }
            $this->blocks = $layoutBlocks;

            // Profiling
            Pi::service('log')->end('BLOCKS');
        }

        $blocks = (null === $zone)
            ? $this->blocks
            : (isset($this->blocks[$zone]) ? $this->blocks[$zone] : array());

        return $blocks;
    }
}
