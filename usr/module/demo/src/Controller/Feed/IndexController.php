<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Controller\Feed;

use Pi;
use Pi\Mvc\Controller\FeedController;

/**
 * Index action controller
 *
 * @see Module\System\Controller\Feed\IndexController
 */
class IndexController extends FeedController
{
    public function indexAction()
    {
        $feed = array(
            'title' =>  'Feed from ' . __METHOD__,
        );

        return $feed;
    }
}
