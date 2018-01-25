<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Block preview controller
 *
 * Usage
 * ```
 *  http://pi.tld/widget/?block=1,2,3&zone=2,6,9&theme=pi
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class IndexController extends ActionController
{
    public function indexAction()
    {
        $block       = _get('block');
        $zone        = _get('zone');
        $theme       = _get('theme');
        $placeholder = [[
                            'title'   => __('Block title'),
                            'content' => __('Block content placeholder.'),
                            'class'   => 'widget-preview-placeholder',

                            'subline'      => null,
                            'title_hidden' => true,
                        ]];

        $parse = function ($input) {
            if (!$input) {
                return false;
            }
            $list = explode(',', $input);
            array_walk($list, 'trim');
            $list = array_filter($list);
            array_walk($list, 'intval');
            $list = array_unique($list);

            return $list;
        };
        $block = $parse($block);
        $zone  = $parse($zone);

        $list        = [];
        $blockLoader = $this->view()->helper('blocks');
        if ($block) {
            $blockRender = $this->view()->helper('block');
            $blocks      = [];
            foreach ($block as $id) {
                $blockRow = $blockRender($id);
                if ($blockRow) {
                    $blocks[] = $blockRow;
                }
            }
            if ($blocks) {
                $zones     = array_keys($blockLoader->getZones());
                $zoneLoad  = $zones;
                $zoneEmpty = [];
                if ($zone) {
                    $zoneLoad  = array_intersect($zones, $zone);
                    $zoneEmpty = array_diff($zones, $zone);
                }
                foreach ($zoneLoad as $key) {
                    $list[$key] = $blocks;
                }
                foreach ($zoneEmpty as $key) {
                    $list[$key] = $placeholder;
                }
            }
            if ($theme) {
                Pi::service('theme')->setTheme($theme);
            }
        }
        $blockLoader->assign($list);

        $this->view()->setTemplate('block-preview');
    }
}
