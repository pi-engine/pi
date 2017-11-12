<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
