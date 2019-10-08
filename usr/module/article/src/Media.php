<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article;

use Pi;

/**
 * Media service API
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Media
{
    protected static $module = 'article';

    /**
     * Get media list
     *
     * @param array $where
     * @param int|null $page
     * @param int|null $limit
     * @param array|null $columns
     * @param string|null $order
     * @param string|null $module
     * @return array
     */
    public static function getList(
        $where = [],
        $page = null,
        $limit = null,
        $columns = null,
        $order = null,
        $module = null
    )
    {
        $module = $module ?: Pi::service('module')->current();
        $model  = Pi::model('media', $module);

        // Assebling select
        $select = $model->select()->where($where);

        if ($page and $limit) {
            $offset = intval($limit) * (intval($page) - 1);
            $select->offset($offset)->limit($limit);
        }

        if ($columns) {
            $select->columns($columns);
        }

        $order = $order ?: 'time_upload DESC';
        $select->order($order);

        // Fetching data
        $rowset       = $model->selectWith($select);
        $mediaSet     = [];
        $submitterIds = $mediaIds = [];
        foreach ($rowset as $row) {
            $item         = $row->toArray();
            $item['size'] = Pi::service('file')->transformSize($item['size']);
            $item['type'] = strtolower($item['type']);
            $meta         = empty($row->meta) ? [] : json_decode($row->meta, true);
            unset($item['meta']);
            $item['previewUrl'] = Pi::service('url')->assemble(
                'default',
                [
                    'module'     => $module,
                    'controller' => 'media',
                    'action'     => 'detail',
                    'id'         => $row->id,
                ]
            );
            $item               = array_merge($item, $meta);
            $mediaSet[$row->id] = $item;
            $mediaIds[]         = $row->id;
            $submitterIds[]     = $row->uid;
        }

        // Fetching stats data
        if (!empty($mediaIds)) {
            $model  = Pi::model('media_stats', $module);
            $rowset = $model->select(['media' => $mediaIds]);
            foreach ($rowset as $row) {
                $id    = $row['media'];
                $stats = $row->toArray();
                unset($stats['id']);
                unset($stats['media']);
                $mediaSet[$id] = array_merge($mediaSet[$id], $stats);
            }
        }

        // Fetching submitter
        $submitter = [];
        if (!empty($submitterIds)) {
            $rowset = Pi::user()
                ->get($submitterIds, ['id', 'name']);
            foreach ($rowset as $row) {
                $submitter[$row['id']] = $row['name'];
            }
            unset($rowset);
        }

        foreach ($mediaSet as &$set) {
            $uid              = $set['uid'];
            $set['submitter'] = isset($submitter[$uid]) ? $submitter[$uid] : '';
            $set['url']       = Pi::url($set['url']);
        }

        return $mediaSet;
    }

    /**
     * Get session instance
     *
     * @param string $module
     * @param string $type
     * @return Pi\Application\Service\Session
     */
    public static function getUploadSession($module = null, $type = 'default')
    {
        $module = $module ?: Pi::service('module')->current();
        $ns     = sprintf('%s_%s_upload', $module, $type);

        return Pi::service('session')->$ns;
    }

    /**
     * Get target directory
     *
     * @param string $section
     * @param string $module
     * @param bool $autoCreate
     * @param bool $autoSplit
     * @return string
     */
    public static function getTargetDir(
        $section,
        $module = null,
        $autoCreate = false,
        $autoSplit = true
    )
    {
        $module  = $module ?: Pi::service('module')->current();
        $config  = Pi::config('', $module);
        $pathKey = sprintf('path_%s', strtolower($section));
        $path    = isset($config[$pathKey]) ? $config[$pathKey] : '';

        if ($autoSplit && !empty($config['sub_dir_pattern'])) {
            $path .= '/' . date($config['sub_dir_pattern']);
        }

        if ($autoCreate) {
            Pi::service('file')->mkdir(Pi::path($path));
        }

        return $path;
    }

    /**
     * Get thumb image name
     *
     * @param string $fileName
     * @return string
     */
    public static function getThumbFromOriginal($fileName)
    {
        $parts = pathinfo($fileName);
        return $parts['dirname']
            . '/' . $parts['filename'] . '-thumb.' . $parts['extension'];
    }

    /**
     * Save image
     *
     * @param array $uploadInfo
     * @return string|bool
     */
    public static function saveImage($uploadInfo)
    {
        $result = false;
        $size   = [];

        $fileName     = $uploadInfo['tmp_name'];
        $absoluteName = Pi::path($fileName);

        $size = [$uploadInfo['w'], $uploadInfo['h']];

        Pi::service('image')->resize($absoluteName, $size);

        // Create thumb
        if (!empty($uploadInfo['thumb_w'])
            or !empty($uploadInfo['thumb_h'])
        ) {
            Pi::service('image')->resize(
                $absoluteName,
                [$uploadInfo['thumb_w'], $uploadInfo['thumb_h']],
                Pi::path(self::getThumbFromOriginal($fileName))
            );
        }

        return $result ? $fileName : false;
    }
}
