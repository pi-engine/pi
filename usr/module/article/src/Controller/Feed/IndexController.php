<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Feed;

use Module\Article\Entity;
use Pi;
use Pi\Mvc\Controller\FeedController;

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
        $page      = $this->params('page', 1);
        $page      = $page > 0 ? $page : 1;
        $limit     = $this->params('limit', 100);
        $limit     = $limit > 500 ? 500 : $limit;
        $timestamp = time();
        $sitename  = Pi::config('sitename');

        $feed = $this->getDataModel([
            'title'        => sprintf(__('All Articles of %s'), $sitename),
            'description'  => sprintf(__('All Articles of %s'), $sitename),
            'copyright'    => $sitename,
            'date_created' => $timestamp,
            'entries'      => [],
        ]);

        $columns = ['id', 'subject', 'time_publish', 'category', 'content'];

        $data = Entity::getAvailableArticlePage(
            null,
            $page,
            $limit,
            $columns,
            null,
            $this->getModule()
        );

        foreach ($data as $row) {
            $entry = [
                'title'         => $row['subject'],
                'date_modified' => (int)$row['time_publish'],
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
            ];

            $feed->entry = $entry;
        }

        return $feed;
    }
}