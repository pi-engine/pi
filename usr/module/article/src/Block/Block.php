<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Block;

use Pi;
use Module\Article\Media;
use Module\Article\Topic;
use Module\Article\Stats;
use Module\Article\Entity;
use Zend\Db\Sql\Expression;

/**
 * Block class for providing article blocks
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Block
{
    /**
     * List all categories and its children
     * 
     * @param array   $options  Block parameters
     * @param string  $module   Module name
     * @return boolean 
     */
    public static function allCategories($options = array(), $module = null)
    {
        if (empty($module)) {
            return false;
        }
        
        $maxTopCount = $options['top-category'];
        $maxSubCount = $options['sub-category'];
        $route = 'article';

        $categories  = Pi::api('api', $module)->getCategoryList(
            array('is-tree' => true)
        );
        
        $allItems = static::canonizeCategories(
            $categories['child'],
            array('route' => $route)
        );
        
        $i = 0;
        foreach ($allItems as $id => &$item) {
            if (++$i > $maxTopCount) {
                unset($allItems[$id]);
            }
            $j = 0;
            if (!isset($item['child'])) {
                continue;
            }
            foreach (array_keys($item['child']) as $subId) {
                if (++$j > $maxSubCount) {
                    unset($item['child'][$subId]);
                }
            }
        }
        
        return array(
            'items'     => $allItems,
            'target'    => $options['target'],
        );
    }
    
    /**
     * List all categories
     * 
     * @param array   $options  Block parameters
     * @param string  $module   Module name
     * @return boolean 
     */
    public static function categoryList($options = array(), $module = null)
    {
        if (empty($module)) {
            return false;
        }
        
        $route = 'article';
        
        // Get all categories
        $categories = array(
            'all' => array(
                'id'    => 0,
                'title' => __('All articles'),
                'depth' => 0,
                'image' => '',
                'url'   => Pi::service('url')->assemble(
                    $route,
                    array(
                        'module'        => $module,
                        'controller'    => 'list',
                        'action'        => 'all',
                        'list'          => 'all',
                    )
                ),
            ),
        );
        $rowset = Pi::model('category', $module)->enumerate(null, null, true);
        foreach ($rowset as $row) {
            if ('root' == $row['name']) {
                continue;
            }
            $url = Pi::service('url')->assemble($route, array(
                'module'        => $module,
                'controller'    => 'category',
                'action'        => 'list',
                'category'      => $row['slug'] ?: $row['id'],
            ));
            $categories[$row['id']] = array(
                'id'    => $row['id'],
                'title' => $row['title'],
                'depth' => $row['depth'],
                'image' => $row['image'],
                'url'   => $url,
            );
        }
        
        $params = Pi::service('url')->getRouteMatch()->getParams();
        
        return array(
            'items'    => $categories,
            'options'  => $options,
            'category' => $params['category'],
        );
    }
    
    /**
     * List hot categories
     * 
     * @param array   $options  Block parameters
     * @param string  $module   Module name
     * @return boolean 
     */
    public static function hotCategories($options = array(), $module = null)
    {
        if (empty($module)) {
            return false;
        }
        
        $limit = (int) $options['list-count'];
        $limit = $limit < 0 ? 0 : $limit;
        $day = (int) $options['day-range'];
        $endDay   = time();
        $startDay = $endDay - $day * 3600 * 24;
        
        // Get category IDs
        $where = array(
            'time_publish > ?'  => $startDay,
            'time_publish <= ?' => $endDay,
        );
        
        $modelArticle = Pi::model('article', $module);
        $select = $modelArticle->select()
            ->where($where)
            ->columns(array('category', 'count' => new Expression('count(*)')))
            ->group(array('category'))
            ->offset(0)
            ->limit($limit)
            ->order('count DESC');
        $rowArticle = $modelArticle->selectWith($select);
        $categoryIds = array(0);
        foreach ($rowArticle as $row) {
            $categoryIds[] = $row['category'];
        }
        
        // Get category Info
        //$route = Pi::api('api', $module)->getRouteName();
        $where = array('id' => $categoryIds);
        $rowCategory = Pi::model('category', $module)->select($where);
        $categories = array();
        foreach ($rowCategory as $row) {
            $categories[$row->id]['title'] = $row->title;
            $categories[$row->id]['url']   = Pi::service('url')->assemble(
                'article',
                array(
                    'module'    => $module,
                    'category'  => $row->slug ?: $row->id,
                )
            );
        }
        
        return array(
            'categories' => $categories,
            'target'     => $options['target'],
        );
    }
    
    /**
     * List newest published articles
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function newestPublishedArticles(
        $options = array(), 
        $module = null
    ) {
        if (empty($module)) {
            return false;
        }
        
        $params   = Pi::service('url')->getRouteMatch()->getParams();
        
        $config   = Pi::config('', $module);
        $image    = $config['default_feature_thumb'];
        $image    = Pi::service('asset')->getModuleAsset($image, $module);
        
        $postCategory = isset($params['category']) ? $params['category'] : 0;
        $postTopic    = isset($params['topic']) ? $params['topic'] : 0;
        
        $category = $options['category'] ? $options['category'] : $postCategory;
        $topic    = $options['topic'] ? $options['topic'] : $postTopic;
        if (!is_numeric($topic)) {
            $topic = Pi::model('topic', $module)->slugToId($topic);
        }
        $limit    = ($options['list-count'] <= 0) ? 10 : $options['list-count'];
        $page     = 1;
        $order    = 'time_update DESC, time_publish DESC';
        $columns  = array('subject', 'summary', 'time_publish', 'image');
        $where    = array();
        if (!empty($category)) {
            $category = Pi::model('category', $module)
                ->getDescendantIds($category);
            $where['category'] = $category;
        }
        if (!empty($options['is-topic'])) {
            if (!empty($topic)) {
                $where['topic'] = $topic;
            }
            $articles = Topic::getTopicArticles(
                $where, 
                $page, 
                $limit, 
                $columns, 
                $order, 
                $module
            );
        } else {
            $articles = Entity::getAvailableArticlePage(
                $where, 
                $page, 
                $limit, 
                $columns, 
                $order, 
                $module
            );
        }
        
        foreach ($articles as &$article) {
            $article['subject'] = mb_substr(
                $article['subject'],
                0,
                $options['max_subject_length'],
                'UTF-8'
            );
            $article['summary'] = mb_substr(
                $article['summary'],
                0,
                $options['max_summary_length'],
                'UTF-8'
            );
            $article['image'] = $article['image'] 
                ? Media::getThumbFromOriginal(Pi::url($article['image']))
                : $image;
        }
        
        return array(
            'articles'  => $articles,
            'target'    => $options['target'],
            'elements'  => (array) $options['element'],
            'config'    => $config,
            'column'    => $options['column-number'],
            'rows'      => $options['description_rows'],
        );
    }
    
    /**
     * List articles defined by user.
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function customArticleList($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        $config   = Pi::config('', $module);
        $image    = $config['default_feature_thumb'];
        $image    = Pi::service('asset')->getModuleAsset($image, $module);
        
        $columns  = array('subject', 'summary', 'time_publish', 'image');
        $ids      = explode(',', $options['articles']);
        foreach ($ids as &$id) {
            $id = trim($id);
        }
        $where    = array('id' => $ids);
        $articles = Entity::getAvailableArticlePage(
            $where, 
            1, 
            10, 
            $columns, 
            null, 
            $module
        );
        
        foreach ($articles as &$article) {
            $article['subject'] = mb_substr(
                $article['subject'],
                0,
                $options['max_subject_length'],
                'UTF-8'
            );
            $article['summary'] = mb_substr(
                $article['summary'],
                0,
                $options['max_summary_length'],
                'UTF-8'
            );
            $article['image'] = $article['image'] 
                ? Media::getThumbFromOriginal(Pi::url($article['image']))
                : $image;
        }
        
        return array(
            'articles'  => $articles,
            'target'    => $options['target'],
            'elements'  => (array) $options['element'],
            'column'    => $options['column-number'],
            'config'    => $config,
            'rows'      => $options['description_rows'],
        );
    }
    
    /**
     * Export a search form.
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function simpleSearch($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        return array(
            'url' => Pi::service('url')->assemble(
                'default',
                array(
                    'module'     => $module,
                    'controller' => 'search',
                    'action'     => 'simple',
                )
            ),
        );
    }

    /**
     * Count all article according to submitter
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function submitterStatistics($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        $limit    = ($options['list-count'] <= 0) ? 10 : $options['list-count'];
        $time      = time();
        $today     = strtotime(date('Y-m-d', $time));
        $tomorrow  = $today + 24 * 3600;
        $week      = $tomorrow - 24 * 3600 * 7;
        $month     = $tomorrow - 24 * 3600 * 30;
        $daySets   = Stats::getSubmittersInPeriod($today, $tomorrow, $limit, $module);
        $weekSets  = Stats::getSubmittersInPeriod($week, $tomorrow, $limit, $module);
        $monthSets = Stats::getSubmittersInPeriod($month, $tomorrow, $limit, $module);
        $historySets = Stats::getSubmittersInPeriod(0, $tomorrow, $limit, $module);
        
        return array(
            'day'     => $daySets,
            'week'    => $weekSets,
            'month'   => $monthSets,
            'history' => $historySets,
        );
    }
    
    /**
     * List newest topics.
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function newestTopic($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        $limit  = ($options['list-count'] <= 0) ? 10 : $options['list-count'];
        $order  = 'id DESC';
        $topics = Topic::getTopics(array(), 1, $limit, null, $order, $module);
        $config = Pi::config('', $module);
        $image  = Pi::service('asset')
            ->getModuleAsset($config['default_topic_thumb'], $module);
        
        foreach ($topics as &$topic) {
            $topic['title'] = mb_substr(
                $topic['title'],
                0,
                $options['max_title_length'],
                'UTF-8'
            );
            $topic['description'] = mb_substr(
                $topic['description'],
                0,
                $options['max_description_length'],
                'UTF-8'
            );
            $topic['image'] = $topic['image'] 
                ? Media::getThumbFromOriginal(Pi::url($topic['image']))
                : $image;
        }
        
        return array(
            'items'     => $topics,
            'target'    => $options['target'],
            'config'    => $config,
        );
    }

    /**
     * List hot articles by visit count
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function hotArticles($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        $limit  = isset($options['list-count']) 
            ? (int) $options['list-count'] : 10;
        $config = Pi::config('', $module);
        $image  = $config['default_feature_thumb'];
        $image  = Pi::service('asset')->getModuleAsset($image, $module);
        $day    = $options['day-range'] ? intval($options['day-range']) : 7;

        if ($options['is-topic']) {
            $params = Pi::service('url')->getRouteMatch()->getParams();
            if (is_string($params)) {
                $params['topic'] = Pi::model('topic', $module)
                    ->slugToId($params['topic']);
            }
            $articles = Topic::getVisitsRecently(
                $day,
                $limit,
                null,
                isset($params['topic']) ? $params['topic'] : null,
                $module
            );
        } else {
            $articles = Entity::getVisitsRecently($day, $limit, null, $module);
        }
        
        foreach ($articles as &$article) {
            $article['subject'] = mb_substr(
                $article['subject'],
                0,
                $options['max_subject_length'],
                'UTF-8'
            );
            $article['summary'] = mb_substr(
                $article['summary'],
                0,
                $options['max_summary_length'],
                'UTF-8'
            );
            $article['image'] = $article['image'] 
                ? Media::getThumbFromOriginal(Pi::url($article['image']))
                : $image;
        }

        return array(
            'articles'  => $articles,
            'target'    => $options['target'],
            'elements'  => (array) $options['element'],
            'column'    => $options['column-number'],
            'config'    => $config,
            'rows'      => $options['description_rows'],
        );
    }
    
    /**
     * List custom articles and with a slideshow besides article list
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function recommendedSlideshow(
        $options = array(),
        $module = null
    ) {
        if (!$module) {
            return false;
        }
        
        // Getting custom article list
        $columns  = array('subject', 'summary', 'time_publish', 'image');
        $ids      = explode(',', $options['articles']);
        foreach ($ids as &$id) {
            $id = trim($id);
        }
        $where    = array('id' => $ids);
        $articles = Entity::getAvailableArticlePage(
            $where,
            1,
            10,
            $columns,
            null,
            $module
        );
        
        $config   = Pi::config('', $module);
        $image    = $config['default_feature_thumb'];
        $image    = Pi::service('asset')->getModuleAsset($image, $module);
        foreach ($articles as &$article) {
            $article['subject'] = mb_substr(
                $article['subject'],
                0,
                $options['max_subject_length'],
                'UTF-8'
            );
            $article['summary'] = mb_substr(
                $article['summary'],
                0,
                $options['max_summary_length'],
                'UTF-8'
            );
            $article['image'] = $article['image'] 
                ? Media::getThumbFromOriginal(Pi::url($article['image']))
                : $image;
        }
        
        // Getting image link url
        $urlRows    = explode('\n', $options['image-link']);
        $imageLinks = array();
        foreach ($urlRows as $row) {
            list($id, $url) = explode(':', trim($row), 2);
            $imageLinks[trim($id)] = trim($url);
        }
        
        // Fetching image ID
        $images   = explode(',', $options['images']);
        $imageIds = array();
        foreach ($images as $key => &$image) {
            $image = trim($image);
            if (is_numeric($image)) {
                $imageIds[] = $image;
            } else {
                $url   = $image ?: 'image/default-recommended.png';
                $image = array(
                    'url'         => Pi::service('asset')->getModuleAsset($url, $module),
                    'link'        => $imageLinks[$key + 1],
                    'title'       => _b('This is default recommended image'),
                    'description' => _b('You should to add your own images and its title and description!'),
                );
            }
        }
        
        if (!empty($imageIds)) {
            $images = array();
            $rowset = Pi::model('media', $module)->select(array('id' => $imageIds));
            foreach ($rowset as $row) {
                $id       = $row['id'];
                $link     = isset($imageLinks[$id]) ? $imageLinks[$id] : '';
                $images[] = array(
                    'url'         => Pi::url($row['url']),
                    'link'        => $link,
                    'title'       => $row['title'],
                    'description' => $row['description'],
                );
            }
        }
        
        return array(
            'articles'  => $articles,
            'target'    => $options['target'],
            'style'     => $options['block-style'],
            'elements'  => (array) $options['element'],
            'height'    => $options['image-height'],
            'images'    => $images,
            'config'    => Pi::config('', $module),
            'rows'      => $options['description_rows'],
        );
    }
    
    /**
     * Show RSS link
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function rss($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        $url = Pi::service('asset')->getModuleAsset(
            $options['default_image'],
            $module
        );
        
        return array(
            'target'      => $options['target'],
            'description' => $options['description'],
            'url'         => $url,
        );
    }
    
    /**
     * Added all sub-categories as children array of top category.
     * 
     * @param array  $categories
     * @param array  $options
     * @return array 
     */
    protected static function canonizeCategories(
        $categories,
        $options = array()
    ) {
        $result = array();
        foreach ($categories as $category) {
            $result[$category['id']] = array(
                'title' => $category['title'],
                'depth' => $category['depth'],
                'url'   => Pi::service('url')->assemble(
                    $options['route'],
                    array(
                        'category'  => $category['slug'] ?: $category['id'],
                    )
                ),
            );
            if (isset($category['child'])) {
                $children = self::canonizeCategories(
                    $category['child'],
                    $options
                );
                if ($category['depth'] > 1) {
                    $result = $result + $children;
                } else {
                    $result[$category['id']]['child'] = $children;
                }
            }
        }
        
        return $result;
    }
}
