<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article;

use Pi;
use Zend\Db\Sql\Expression;
use Module\Article\Model\Article;
use Module\Article\Compiled;
use Module\Article\Stats;
use Module\Article\Draft;
use Module\Article\Model\Stats as ModelStats;
use Module\Article\Model\Draft as DraftModel;

/**
 * Article service APIs
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Entity
{
    protected static $module = 'article';

    /**
     * Get recently visited articles
     * 
     * @param int     $dateFrom
     * @param int     $dateTo
     * @param int     $limit
     * @param int     $category
     * @param string  $module
     * @return array 
     */
    public static function getVisitsInPeriod(
        $dateFrom, 
        $dateTo, 
        $limit = null, 
        $category = null, 
        $module = null
    ) {
        $result = $where = array();
        $module = $module ?: Pi::service('module')->current();

        $modelArticle   = Pi::model('article', $module);
        $modelCategory  = Pi::model('category', $module);
        $modelVisit     = Pi::model('visit', $module);

        if (!empty($dateFrom)) {
            $where['time >= ?'] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where['time <= ?'] = $dateTo;
        }

        if ($category && $category > 1) {
            $categoryIds = $modelCategory->getDescendantIds($category);

            if ($categoryIds) {
                $where['a.category'] = $categoryIds;
            }
        }

        $where['status'] = Article::FIELD_STATUS_PUBLISHED;
        $where['active'] = 1;

        $select = $modelVisit->select()
            ->columns(
                array(
                    'article',
                    'total' => new Expression('count(article)')
                )
            )
            ->join(
                array('a' => $modelArticle->getTable()),
                sprintf('%s.article = a.id', $modelVisit->getTable()),
                array()
            )
            ->where($where)
            ->group(array(sprintf('%s.article', $modelVisit->getTable())))
            ->order('total DESC');

        if ($limit) {
            $select->limit($limit);
        }

        $resultsetVisit = $modelVisit->selectWith($select)->toArray();
        foreach ($resultsetVisit as $row) {
            $result[$row['article']] = $row;
        }

        $articleIds = array_keys($result);
        if ($articleIds) {
            $resultsetArticle = self::getAvailableArticlePage(
                array('id' => $articleIds), 
                1, 
                $limit, 
                null, 
                '', 
                $module
            );

            foreach ($result as $key => &$row) {
                if (isset($resultsetArticle[$key])) {
                    $row = $row + $resultsetArticle[$key];
                }
            }
        }

        return $result;
    }

    /**
     * Get recently visited articles
     * 
     * @param int     $days
     * @param int     $limit
     * @param int     $category
     * @param string  $module
     * @return array 
     */
    public static function getVisitsRecently(
        $days,
        $limit = null,
        $category = null,
        $module = null
    ) {
        $dateTo   = time();
        $dateFrom = $dateTo - 24 * 3600 * $days;

        return self::getVisitsInPeriod(
            $dateFrom, 
            $dateTo, 
            $limit, 
            $category, 
            $module
        );
    }

    /**
     * Get total visits of articles.
     * 
     * @param int     $limit
     * @param int     $category
     * @param string  $module
     * @return array 
     */
    public static function getTotalVisits(
        $limit = null, 
        $category = null, 
        $module = null
    ) {
        $where = $columns = array();
        $module = $module ?: self::$module;

        $modelCategory  = Pi::model('category', $module);
        if ($category && $category > 1) {
            $categoryIds = $modelCategory->getDescendantIds($category);

            if ($categoryIds) {
                $where['category'] = $categoryIds;
            }
        }
        
        $articles    = Stats::getTopVisits($limit, $module);
        if (!empty($articles)) {
            $where['id'] = array_keys($articles);
        }
        
        $columns = array(
            'id',
            'article' => 'id',
            'subject',
            'source',
            'image',
            'pages',
            'summary',
            'time_publish',
            'visits',
        );

        $result = self::getAvailableArticlePage(
            $where, 
            1, 
            $limit, 
            $columns, 
            null, 
            $module
        );
        
        foreach ($articles as $id => &$article) {
            $article = array_merge($article, $result[$id]);
        }

        return $articles;
    }

    /**
     * Get latest articles
     * 
     * @param int    $limit
     * @param int    $category
     * @param string $module
     * @return array
     */
    public static function getLatest(
        $limit = null, 
        $category = null, 
        $module = null
    ) {
        $where = $columns = array();
        $module = $module ?: self::$module;

        $modelCategory  = Pi::model('category', $module);
        if ($category && $category > 1) {
            $categoryIds = $modelCategory->getDescendantIds($category);

            if ($categoryIds) {
                $where['category'] = $categoryIds;
            }
        }

        $columns = array(
            'id',
            'article' => 'id',
            'total'   => 'visits',
            'subject',
            'source',
            'image',
            'pages',
            'slug',
            'summary',
            'time_publish',
        );

        $result = self::getAvailableArticlePage(
            $where, 
            1, 
            $limit, 
            $columns, 
            null,
            $module
        );

        return $result;
    }

    /**
     * Get published article details
     * 
     * @param array   $where
     * @param int     $page
     * @param int     $limit
     * @param array   $columns
     * @param string  $order
     * @param string  $module
     * @return array 
     */
    public static function getArticlePage(
        $where, 
        $page, 
        $limit, 
        $columns = null, 
        $order = null, 
        $module = null
    ) {
        $offset = ($limit && $page) ? $limit * ($page - 1) : null;

        $module = $module ?: Pi::service('module')->current();
        $config = Pi::config('', $module);
        $articleIds = $userIds = $authorIds = $categoryIds = array();
        $categories = $authors = $users = $tags = $urls = array();

        $modelArticle  = Pi::model('article', $module);
        
        // Generate columns of extended table and statistics table
        $extendedColumns = Pi::service('registry')
            ->handler('extended', $module)
            ->read($module);
        $statisColumns = ModelStats::getAvailableColumns();
        if (!empty($columns)) {
            // Get needed columns of extended table
            foreach ($extendedColumns as $key => $col) {
                if (!in_array($col, $columns)) {
                    unset($extendedColumns[$key]);
                }
            }
            // Get needed columns of statistics table
            foreach ($statisColumns as $key => $col) {
                if (!in_array($col, $columns)) {
                    unset($statisColumns[$key]);
                }
            }
            // Remove fields not belong to article table
            $columns = array_diff($columns, $extendedColumns);
            $columns = array_diff($columns, $statisColumns);
        }

        $resultset = $modelArticle->getSearchRows(
            $where, 
            $limit, 
            $offset, 
            $columns, 
            $order
        );

        if ($resultset) {
            foreach ($resultset as $row) {
                $articleIds[] = $row['id'];

                if (!empty($row['author'])) {
                    $authorIds[] = $row['author'];
                }

                if (!empty($row['uid'])) {
                    $userIds[] = $row['uid'];
                }
            }
            $authorIds = array_unique($authorIds);
            $userIds   = array_unique($userIds);
            
            // Getting statistics data
            $templateStatis = array();
            if (!empty($statisColumns)) {
                $statisColumns[] = 'id';
                $statisColumns[] = 'article';
                $modelStatis = Pi::model('stats', $module);
                $select      = $modelStatis
                    ->select()
                    ->where(array('article' => $articleIds))
                    ->columns($statisColumns);
                $rowStatis   = $modelStatis->selectWith($select);
                $statis      = array();
                foreach ($rowStatis as $item) {
                    $temp = $item->toArray();
                    unset($temp['id']);
                    unset($temp['article']);
                    $statis[$item->article] = $temp;
                }
                foreach ($statisColumns as $col) {
                    if (in_array($col, array('id', 'article'))) {
                        continue;
                    }
                    $templateStatis[$col] = null;
                }
            }
            
            // Default extended columns
            $extendedColumns = array_merge(
                $extendedColumns,
                array('slug', 'seo_title', 'seo_keywords', 'seo_description')
            );
            $extendedColumns = array_unique($extendedColumns);
            // Getting extended data
            $templateExtended = array();
            if (!empty($extendedColumns)) {
                $extendedColumns[] = 'id';
                $extendedColumns[] = 'article';
                $modelExtended = Pi::model('extended', $module);
                $select        = $modelExtended
                    ->select()
                    ->where(array('article' => $articleIds))
                    ->columns($extendedColumns);
                $rowExtended   = $modelExtended->selectWith($select);
                $extended      = array();
                foreach ($rowExtended as $item) {
                    $temp = $item->toArray();
                    unset($temp['id']);
                    unset($temp['article']);
                    $extended[$item->article] = $temp;
                }
                foreach ($extendedColumns as $col) {
                    if (in_array($col, array('id', 'article'))) {
                        continue;
                    }
                    $templateExtended[$col] = null;
                }
            }

            $categories = Pi::api('api', $module)->getCategoryList();

            if (!empty($authorIds) 
                && (empty($columns) || in_array('author', $columns))
            ) {
                $resultsetAuthor = Pi::api('api', $module)->getAuthorList($authorIds);
                foreach ($resultsetAuthor as $row) {
                    $authors[$row['id']] = array(
                        'name' => $row['name'],
                    );
                }
                unset($resultsetAuthor);
            }

            if (!empty($userIds) 
                && (empty($columns) || in_array('uid', $columns))
            ) {
                $resultsetUser = Pi::user()
                    ->get($userIds, array('id', 'name'));
                foreach ($resultsetUser as $row) {
                    $users[$row['id']] = array(
                        'name' => $row['name'],
                    );
                }
                unset($resultsetUser);
            }

            /*
            if (!empty($articleIds)) {
                if ((empty($columns) || in_array('tag', $columns))
                    && $config['enable_tag']
                ) {
                    $tags = Pi::service('tag')->get($module, $articleIds);
                }
            }
            */

            foreach ($resultset as &$row) {
                if (empty($columns) || in_array('category', $columns)) {
                    if (!empty($categories[$row['category']])) {
                        $row['category_title'] = $categories[$row['category']]['title'];
                        $row['category_slug']  = $categories[$row['category']]['slug'];
                    }
                }

                if (empty($columns) || in_array('uid', $columns)) {
                    if (!empty($users[$row['uid']])) {
                        $row['user_name'] = $users[$row['uid']]['name'];
                    }
                }

                if (empty($columns) || in_array('author', $columns)) {
                    if (!empty($authors[$row['author']])) {
                        $row['author_name'] = $authors[$row['author']]['name'];
                    }
                }

                if (empty($columns) || in_array('image', $columns)) {
                    if ($row['image']) {
                        $row['thumb'] = Media::getThumbFromOriginal($row['image']);
                    }
                }

                /*
                if ((empty($columns) 
                    || in_array('tag', $columns)) && $config['enable_tag']) {
                    if (!empty($tags[$row['id']])) {
                        $row['tag'] = $tags[$row['id']];
                    }
                }
                */

                if (empty($columns) || in_array('subject', $columns)) {
                    //$route = Pi::api('api', $this->module)->getRouteName();
                    $route = 'article';
                    $row['url'] = Pi::service('url')->assemble($route, array(
                        'module'    => $module,
                        'time'      => date('Ymd', $row['time_publish']),
                        'id'        => $row['id'],
                        'slug'      => $extended[$row['id']]['slug'],
                    ));
                }
                
                if (!isset($statis[$row['id']])) {
                    $statis[$row['id']] = $templateStatis;
                }
                $row = array_merge($row, $statis[$row['id']]);
                if (!isset($extended[$row['id']])) {
                    $extended[$row['id']] = $templateExtended;
                }
                $row = array_merge($row, $extended[$row['id']]);
            }
        }

        return $resultset;
    }

    /**
     * Get available articles which are published and active
     * 
     * @param array   $where
     * @param int     $page
     * @param int     $limit
     * @param array   $columns
     * @param string  $order
     * @param string  $module
     * @return array 
     */
    public static function getAvailableArticlePage(
        $where, 
        $page, 
        $limit, 
        $columns = null, 
        $order = null,
        $module = null
    ) {
        $defaultWhere = array(
            'time_publish <= ?' => time(),
            'status'            => Article::FIELD_STATUS_PUBLISHED,
            'active'            => 1,
        );
        $where = $where ? array_merge($where, $defaultWhere) : $defaultWhere;

        return self::getArticlePage($where, $page, $limit, $columns, $order, $module);
    }

    /**
     * Get published article details
     * 
     * @param int  $id  Article ID
     * @return array 
     */
    public static function getEntity($id)
    {
        $module = Pi::service('module')->current();
        $config = Pi::config('', $module);

        $row = Pi::model('article', $module)->find($id);
        if (empty($row->id)) {
            return array();
        }
        if ($row->markup) {
            $subject  = Pi::service('markup')->render($row->subject, 'html', $row->markup);
            $subtitle = Pi::service('markup')->render($row->subtitle, 'html', $row->markup);
        } else {
            $subject  = Pi::service('markup')->render($row->subject, 'html');
            $subtitle = Pi::service('markup')->render($row->subtitle, 'html');
        }
        $content = Compiled::getContent($row->id, 'html');

        $result  = array(
            'title'         => $subject,
            'content'       => Draft::breakPage($content),
            'subtitle'      => $subtitle,
            'source'        => $row->source,
            'pages'         => $row->pages,
            'time_publish'  => $row->time_publish,
            'active'        => $row->active,
            'visits'        => '',
            'slug'          => '',
            'seo'           => array(),
            'author'        => array(),
            'attachment'    => array(),
            'tag'           => '',
            'related'       => array(),
        );

        // Get author
        if ($row->author) {
            $author = Pi::api('api', $module)->getAuthorList((array) $row->author);

            if ($author) {
                $result['author'] = array_shift($author);
                if (empty($result['author']['photo'])) {
                    $result['author']['photo'] = 
                        Pi::service('asset')->getModuleAsset(
                            $config['default_author_photo'], 
                            $module
                        );
                }
            }
        }

        // Get attachments
        $resultsetAsset = Pi::model('asset', $module)->select(array(
            'article'   => $id,
            'type'      => 'attachment',
        ));
        $mediaIds = array();
        foreach ($resultsetAsset as $asset) {
            $mediaIds[$asset->media] = $asset->media;
        }
        if ($mediaIds) {
            $resultsetMedia = Pi::model('media', $module)->select(
                array('id' => $mediaIds)
            );

            foreach ($resultsetMedia as $media) {
                $result['attachment'][] = array(
                    'original_name' => $media->title,
                    'extension'     => $media->type,
                    'size'          => $media->size,
                    'url'           => Pi::service('url')->assemble(
                        'default',
                        array(
                            'module'     => $module,
                            'controller' => 'media',
                            'action'     => 'download',
                            'id'         => $media->id,
                        )
                    ),
                );
            }
        }

        // Get tag
        /*
        if ($config['enable_tag']) {
            $result['tag'] = Pi::service('tag')->get($module, $id);
        }
        */

        // Get related articles
        $relatedIds = Pi::model('related', $module)->getRelated($id);

        if ($relatedIds) {
            $related = array_flip($relatedIds);
            $where   = array('id' => $relatedIds);
            $columns = array('id', 'subject', 'time_publish');

            $resultsetRelated = self::getArticlePage(
                $where, 
                1, 
                null, 
                $columns, 
                null, 
                $module
            );

            foreach ($resultsetRelated as $key => $val) {
                if (array_key_exists($key, $related)) {
                    $related[$key] = $val;
                }
            }

            $result['related'] = array_filter($related, function($var) {
                return is_array($var);
            });
        }

        // Getting seo
        $modelExtended  = Pi::model('extended', $module);
        $rowExtended    = $modelExtended->find($row->id);
        if ($rowExtended) {
            $result['slug'] = $rowExtended->slug;
            $result['seo']  = array(
                'title'        => $rowExtended->seo_title,
                'keywords'     => $rowExtended->seo_keywords,
                'description'  => $rowExtended->seo_description,
            );
        }
        
        // Getting stats data
        $modelStatis    = Pi::model('stats', $module);
        $rowStatis      = $modelStatis->find($row->id);
        if ($rowStatis) {
            $result['visits'] = $rowStatis->visits;
        }

        return $result;
    }

    /**
     * Get count statistics of draft with different status and published article
     *
     * @param string $from
     * @param array  $rules
     *
     * @return array
     */
    public static function getSummary($from = 'my', $rules = array())
    {
        // Processing user management category
        $categories = array_keys($rules);
                    
        $module = Pi::service('module')->current();
        
        $result = array(
            'published' => 0,
            'draft'     => 0,
            'pending'   => 0,
            'rejected'  => 0,
        );

        $where['article < ?'] = 1;
        if ('my' == $from) {
            $where['uid'] = Pi::user()->getId() ?: 0;
        }
        $modelDraft = Pi::model('draft', $module);
        $select     = $modelDraft->select()
            ->columns(array(
                'status', 
                'total' => new Expression('count(status)'), 
                'category'
            ))
            ->where($where)
            ->group(array('status', 'category'));
        $resultset  = $modelDraft->selectWith($select);
        foreach ($resultset as $row) {
            if (DraftModel::FIELD_STATUS_DRAFT == $row->status) {
                $result['draft'] += $row->total;
            } else if (DraftModel::FIELD_STATUS_PENDING == $row->status) {
                if ('all' == $from 
                    and in_array($row->category, $categories)
                ) {
                    $result['pending'] += $row->total;
                } elseif ('my' == $from) {
                    $result['pending'] += $row->total;
                }
            } else if (DraftModel::FIELD_STATUS_REJECTED == $row->status) {
                $result['rejected'] += $row->total;
            }
        }

        $modelArticle = Pi::model('article', $module);
        $where        = array(
            'status'   => Article::FIELD_STATUS_PUBLISHED,
            'category' => !empty($categories) ? $categories : 0,
        );
        if ('my' == $from) {
            $where['uid'] = Pi::user()->getId() ?: 0;
        }
        $count = $modelArticle->count($where);
        if ($count) {
            $result['published'] = $count;
        }

        return $result;
    }
}
