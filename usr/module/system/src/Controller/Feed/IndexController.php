<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Feed;

use Pi;
use Pi\Feed\Model as DataModel;
use Pi\Mvc\Controller\FeedController;
use Zend\Db\RowGateway\AbstractRowGateway;

/**
 * Feed action controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class IndexController extends FeedController
{
    /**
     * Create feeds for recent module updates
     *
     * @return DataModel
     */
    public function indexAction()
    {
        $feed = $this->getDataModel([
            'title'        => __('What\'s new'),
            'description'  => __('Recent module feeds.'),
            'date_created' => time(),
        ]);

        $moduleList = Pi::registry('modulelist')->read();
        unset($moduleList['system']);

        foreach ($moduleList as $module) {
            $feedClass = sprintf('Module\%s\Controller\Feed\IndexController', ucfirst($module['name']));
            if (class_exists($feedClass)) {
                $entry       = [
                    'title'         => $module['title'],
                    'description'   => sprintf(__('Resent feeds of %s module'), $module['title']),
                    'date_modified' => time(),
                    'link'          => $this->getHref($module),
                ];
                $feed->entry = $entry;
            }
        }

        return $feed;
    }

    /**
     * Get href of a feed entry
     *
     * @param AbstractRowGateway $row
     * @return string
     */
    protected function getHref($module)
    {
        $uri = sprintf('feed/%s', $module['name']);
        return Pi::url($uri, true);
    }
}