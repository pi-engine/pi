<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Article\Stats;
use Module\Article\Entity;

/**
 * Stats controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class StatsController extends ActionController
{
    /**
     * Default page 
     */
    public function indexAction()
    {
        $topVisitsEver = Entity::getTotalVisits(10);
        $topVisits7    = Entity::getVisitsRecently(7, 10);
        $topVisits30   = Entity::getVisitsRecently(30, 10);

        $totalEver = Stats::getTotalRecently();
        $total7    = Stats::getTotalRecently(7);
        $total30   = Stats::getTotalRecently(30);

        $totalEverByCategory = Stats::getTotalRecentlyByCategory();
        $total7ByCategory    = Stats::getTotalRecentlyByCategory(7);
        $total30ByCategory   = Stats::getTotalRecentlyByCategory(30);

        $topSubmittersEver = Stats::getSubmittersRecently(null, 10);
        $topSubmitters7    = Stats::getSubmittersRecently(7, 10);
        $topSubmitters30   = Stats::getSubmittersRecently(30, 10);

        if ($this->config('enable_tag') && Pi::service('tag')->active()) {
            $topTags = Pi::service('tag')->top(10, $this->getModule(), null);
            $this->view()->assign('topTags', $topTags);
        }

        $this->view()->assign(array(
            'title'               => _a('Statistic'),

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
