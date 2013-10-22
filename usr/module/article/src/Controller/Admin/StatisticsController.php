<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Pi;
use Module\Article\Statistics;
use Module\Article\Entity;

/**
 * Statistics controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class StatisticsController extends ActionController
{
    /**
     * Default page 
     */
    public function indexAction()
    {
        $topVisitsEver = Entity::getTotalVisits(10);
        $topVisits7    = Entity::getVisitsRecently(7, 10);
        $topVisits30   = Entity::getVisitsRecently(30, 10);

        $totalEver = Statistics::getTotalRecently();
        $total7    = Statistics::getTotalRecently(7);
        $total30   = Statistics::getTotalRecently(30);

        $totalEverByCategory = Statistics::getTotalRecentlyByCategory();
        $total7ByCategory    = Statistics::getTotalRecentlyByCategory(7);
        $total30ByCategory   = Statistics::getTotalRecentlyByCategory(30);

        $topSubmittersEver = Statistics::getSubmittersRecently(null, 10);
        $topSubmitters7    = Statistics::getSubmittersRecently(7, 10);
        $topSubmitters30   = Statistics::getSubmittersRecently(30, 10);

        if ($this->config('enable_tag')) {
            $topTags = Pi::service('api')->tag->top($this->getModule(), null, 10);
            $this->view()->assign('topTags', $topTags);
        }

        $this->view()->assign(array(
            'title'               => __('Statistic'),

            'topVisitsEver'       => $topVisitsEver,
            'topVisits7'          => $topVisits7,
            'topVisits30'         => $topVisits30,

            'totalEver'           => $totalEver,
            'total7'              => $total7,
            'total30'             => $total30,

            'totalEverByCategory' => $totalEverByCategory,
            'total7ByCategory'    => $total7ByCategory,
            'total30ByCategory'   => $total30ByCategory,

            'topSubmittersEver'   => $topSubmittersEver,
            'topSubmitters7'      => $topSubmitters7,
            'topSubmitters30'     => $topSubmitters30,
        ));
    }
}
