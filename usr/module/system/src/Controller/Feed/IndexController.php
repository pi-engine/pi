<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Feed;

use Pi;
use Pi\Mvc\Controller\FeedController;
use Pi\Feed\Model as DataModel;
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
        $feed = $this->getDataModel(array(
            'title'         => __('What\'s new'),
            'description'   => __('Recent module updates.'),
            'date_created'  => time(),
        ));
        $model = $this->getModel('update');
        $select = $model->select()->order('time DESC')->limit(10);
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $entry = array();
            $entry['title'] = $row->title;
            $entry['description'] = $row->content;
            $entry['date_modified'] = (int) $row->time;
            $entry['link'] = $this->getHref($row);
            $feed->entry = $entry;
        }

        return $feed;
    }

    /**
     * Get href of a feed entry
     *
     * @param AbstractRowGateway $row
     * @return string
     */
    protected function getHref($row)
    {
        $uri = $row->uri
            ?: $this->url(
                $row->route ? $row->route : 'default',
                array(
                    'module'        => $row->module,
                    'controller'    => $row->controller,
                    'action'        => $row->action,
                    'params'        => empty($row->params)
                        ? array() : parse_str($row->params)
                )
            );

        return Pi::url($uri, true);
    }
}
