<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;

/**
 * List of blocks on pages of a module
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Block extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = [])
    {
        $model  = Pi::model('page');
        $module = $options['module'];
        $role   = isset($options['role']) ? $options['role'] : null;

        $pageList = $model->select([
            'module'  => $module,
            'section' => 'front',
            'block'   => 1,
        ]);
        // Created page list indexed by module-controller-action
        $pages = [];
        foreach ($pageList as $page) {
            $key = $module;
            if (!empty($page['controller'])) {
                $key .= '-' . $page['controller'];
                if (!empty($page['action'])) {
                    $key .= '-' . $page['action'];
                }
            }
            $pages[$key] = $page['id'];
        }

        if (empty($pages)) {
            return [];
        }

        $modelLinks = Pi::model('page_block');
        $select     = $modelLinks->select()->order(['zone', 'order'])
            ->where(['page' => array_values($pages)]);
        $blockLinks = $modelLinks->selectWith($select)->toArray();
        $blocksId   = [];

        // Get all block Ids
        foreach ($blockLinks as $link) {
            $blocksId[$link['block']] = 1;
        }
        // Check for active for blocks
        if (!empty($blocksId)) {
            $modelBlock = Pi::model('block');
            $select     = $modelBlock->select()->columns(['id'])
                ->where(['id' => array_keys($blocksId), 'active' => 0]);
            $rowset     = $modelBlock->selectWith($select);
            foreach ($rowset as $row) {
                unset($blocksId[$row->id]);
            }
            $blocksId = array_keys($blocksId);
        }

        // Filter blocks via permission check
        if ($role
            && !Pi::permission()->isAdminRole($role)
            && !empty($blocksId)
        ) {
            $blocksAllowed = Pi::service('permission')->blockList($blocksId, $role);
        } else {
            $blocksAllowed = $blocksId;
        }

        // Reorganize blocks by page and zone
        $blocksByPageZone = [];
        foreach ($blockLinks as $link) {
            // Skip inactive blocks
            if (!in_array($link['block'], $blocksId)) {
                continue;
            }
            if (null === $blocksAllowed
                || in_array($link['block'], $blocksAllowed)
            ) {
                if (!isset($blocksByPageZone[$link['page']][$link['zone']])) {
                    $blocksByPageZone[$link['page']][$link['zone']] = [];
                }
                $blocksByPageZone[$link['page']][$link['zone']][]
                    = $link['block'];
            }
        }

        foreach ($pages as $key => &$item) {
            if (isset($blocksByPageZone[$item])) {
                $item = $blocksByPageZone[$item];
            } else {
                $item = [];
            }
        }

        return $pages;
    }

    /**
     * {@inheritDoc}
     * @param string $module
     * @param string|null $role
     */
    public function read($module = '', $role = null)
    {
        //$this->cache = false;
        $role   = $this->canonizeRole($role);
        $module = $module ?: Pi::service('module')->current();
        if ($role) {
            $options = compact('module', 'role');
        } else {
            $options = compact('module');
        }

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param string $module
     * @param string|null $role
     */
    public function create($module = '', $role = null)
    {
        $module = $module ?: Pi::service('module')->current();
        $this->clear($module);
        $this->read($module, $role);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->clear('');
        $this->flushByModules();

        return $this;
    }
}
