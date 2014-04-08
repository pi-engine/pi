<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Stats controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class StatsController extends ActionController
{
    /**
     * Analysis media data
     * 
     * @return ViewModel 
     */
    public function indexAction()
    {
        $module = $this->getModule();
        
        // Get top 10 media resources
        $topMedias = Pi::api('stats', $module)->getTopTotal(10);
        
        // Get top submitter
        $userSubmitToday = $this->getTopSubmitter(1, 10);
        $userSubmit7     = $this->getTopSubmitter(7, 10);
        $userSubmit30    = $this->getTopSubmitter(30, 10);
        $userSubmitEver  = $this->getTopSubmitter(null, 10);
        
        $this->view()->assign(array(
            'title'      => _a('Statistic'),
            'medias'     => $topMedias,
            'user'       => array(
                'today'  => $userSubmitToday,
                'week'   => $userSubmit7,
                'month'  => $userSubmit30,
                'ever'   => $userSubmitEver,
            ),
        ));
    }
    
    /**
     * Get top submitter
     * 
     * @param int $days
     * @param int $limit
     * @param array $where
     * @return array
     */
    protected function getTopSubmitter($days, $limit, $where = array())
    {
        $module = $this->getModule();
        
        $where = array(
            'active'       => 1,
            'time_deleted' => 0,
        );
        $result = Pi::api('stats', $module)->getTopSubmitterInPeriod(
            $days,
            $limit,
            $where
        );
        foreach ($result as &$row) {
            $row['title'] = $row['identity'];
        }
        
        return $result;
    }
}
