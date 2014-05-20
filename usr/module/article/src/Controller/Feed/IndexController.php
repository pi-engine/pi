<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Feed;

use Pi;
use Pi\Mvc\Controller\FeedController;
use Module\Article\Entity;

/**
 * Controller for providing RSS
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class IndexController extends FeedController
{
    /**
     * Default action to generate RSS info
     */
    public function indexAction()
    {
        $page = $this->params('page', 1);
        $page = $page > 0 ? $page : 1;
        $limit = $this->params('limit', 100);
        $limit = $limit > 500 ? 500 : $limit;
        $timestamp = time();
        $sitename = Pi::config('sitename');

        $feed = $this->getDataModel(array(
            'title'         => sprintf(__('All Articles of %s'), $sitename),
            'description'   => sprintf(__('All Articles of %s'), $sitename),
            'copyright'     => $sitename,
            'date_created'  => $timestamp,
            'entries'       => array(),
        ));

        $columns = array('id', 'subject', 'time_publish', 'category', 'content');

        $data = Entity::getAvailableArticlePage(
            null,
            $page,
            $limit,
            $columns,
            null,
            $this->getModule()
        );

        foreach ($data as $row) {
            $entry = array(
                'title'         => $row['subject'],
                'date_modified' => (int) $row['time_publish'],
                'channel'       => $row['channel_title'],
                'category'      => $row['category_title'] ?: '&nbsp;',
                /*
                'link'          => sprintf(
                    'http://%s/%s',
                    $_SERVER['HTTP_HOST'],
                    ltrim($row['url'], '/')
                ),
                */
                'link'          => Pi::url($row['url'], true),
                'description'   => $row['content'] ?: '&nbsp;',
            );

            $feed->entry = $entry;
        }

        return $feed;
    }
}