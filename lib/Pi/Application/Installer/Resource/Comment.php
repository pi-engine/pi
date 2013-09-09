<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Installer\Resource;

use Pi;

/**
 * Comment meta setup
 *
 * Comment data registered to comment module
 *
 * <code>
 *  <category-name>  => array(
 *      'title'         => __('Comment category title'),
 *      'callback'      => <callback>
 *      'icon'          => <img-src>,
 *      'controller'    => <controller-name>,
 *      'action'        => <action-name>,
 *      'identifier'    => <item-identifier-name>,
 *      'params'        => array(
 *          <extra-param>   => <param-value>,
 *          <...>,
 *      ),
 *  ),
 *  <...>
 * </code>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Comment extends AbstractResource
{
    protected $categoryColumn = array(
        'id',
        'title',
        'callback',
        'module',
        'controller',
        'action',
        'identifier',
        'params',
        'active',
        'icon'
    );

    /**
     * Check if comment spec is applicable
     *
     * @return bool
     */
    protected function isActive()
    {
        return Pi::service('module')->isActive('comment') ? true : false;
    }

    /**
     * Canonize comment specs
     *
     * @param array $config
     * @return array
     */
    protected function canonize($config)
    {
        $result = array();
        foreach ($config as $category => $data) {
            foreach ($data as $key => $val) {
                if (!in_array($key, $this->categoryColumn)) {
                    unset($data[$key]);
                }
            }
            if (!isset($data['module'])) {
                $data['module'] = $this->getModule();
            }
            if (!isset($data['controller'])) {
                $data['controller'] = 'index';
            }
            if (!isset($data['action'])) {
                $data['action'] = 'index';
            }
            if (!isset($data['identifier'])) {
                $data['identifier'] = 'id';
            }
            if (!isset($data['name'])) {
                $data['name'] = $category;
            }
            if (!isset($data['callback'])) {
                $data['callback'] = sprintf(
                    'Module\\%s\Comment\%s',
                    ucfirst($this->event->getParam('directory')),
                    ucfirst($category)
                );
            }
            $result[$category] = $data;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        if ('comment' != $this->getModule() && !$this->isActive()) {
            return;
        }
        if (empty($this->config)) {
            return;
        }
        Pi::registry('category', 'comment')->clear();

        $model = Pi::model('category', 'comment');
        $config = $this->canonize($this->config);
        foreach ($config as $key => $spec) {
            $row = $model->createRow($spec);
            $status = $row->save();
            if (!$status) {
                return array(
                    'status'    => false,
                    'message'   => sprintf(
                        '"%s" is not created.',
                        $key
                    ),
                );
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        if (!$this->isActive()) {
            return;
        }
        $module = $this->getModule();
        Pi::registry('category', 'comment')->clear();

        if ($this->skipUpgrade()) {
            return;
        }

        $itemsDeleted = array();
        $items = $this->canonize($this->config);
        $model = Pi::model('category', 'comment');
        $rowset = $model->select(array('module' => $module));
        foreach ($rowset as $row) {
            $key = $row->name;
            // Update existent item
            if (isset($items[$key])) {
                // Active status is editable, don't overwrite
                unset($items[$key]['active']);

                $row->assign($items[$key]);
                $row->save();
                unset($items[$key]);

            // Delete deprecated items
            } else {
                $itemsDeleted[] = $key;
                $row->delete();
            }
        }
        // Add new items
        foreach ($items as $key => $spec) {
            $row = $model->createRow($spec);
            $status = $row->save();
            if (!$status) {
                return array(
                    'status'    => false,
                    'message'   => sprintf(
                        '"%s" is not created.',
                        $key
                    ),
                );
            }
        }

        // Delete deprecated comments
        if ($itemsDeleted) {
            $categories = "'" . implode("','", $itemsDeleted) . "'";
            $modelRoot = Pi::model('root', 'comment');
            $modelPost = Pi::model('post', 'comment');
            $sql = 'DELETE post FROM %s AS post'
                 . ' LEFT JOIN %s AS root'
                 . ' ON root.id=post.root'
                 . ' WHERE post.module=\'%s\' AND root.category IN(%s)';
            $sql = sprintf(
                $sql,
                $modelPost->getTable(),
                $modelRoot->getTable(),
                $categories,
                $module
            );
            Pi::db()->query($sql);
            $modelRoot->delete(array(
                'module'    => $module,
                'category'  => $itemsDeleted
            ));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->getModule();

        if (!$this->isActive() || 'comment' == $module) {
            return;
        }
        Pi::registry('category', 'comment')->clear();

        Pi::model('category', 'comment')->delete(array('module' => $module));
        Pi::model('root', 'comment')->delete(array('module' => $module));
        Pi::model('post', 'comment')->delete(array('module' => $module));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        if (!$this->isActive()) {
            return;
        }
        $module = $this->getModule();
        Pi::registry('category', 'comment')->clear();

        $model = Pi::model('category', 'comment');
        $model->update(array('active' => 1), array('module' => $module));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        if (!$this->isActive()) {
            return;
        }
        $module = $this->getModule();
        Pi::registry('category', 'comment')->clear();

        $model = Pi::model('category', 'comment');
        $model->update(array('active' => 0), array('module' => $module));

        return true;
    }
}
