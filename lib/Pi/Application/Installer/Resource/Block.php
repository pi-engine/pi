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
use Pi\Application\Model\Block\Root as RootRow;

/**
 * Block maintenance with configuration specs
 *
 * ```
 *  array(
 *      // Block with renderer and structured options
 *      'blockA' => array(
 *          // Required, translated
 *          'title'         => __('Block Title'),
 *          // Optional
 *          //'link'          => '/link/to/a/URL',
 *          // Optional, specified stylesheet class for display
 *          //'class'         => 'css-class',
 *          // Optional, translated
 *          'description'   => __('Desribing the block'),
 *           // Required
 *          'render'        => array('class', 'method'),
 *          // in module/template/block/, no suffix
 *          'template'      => 'template',
 *          // Cache level type, optional: role, locale, user
 *          'cache_level'   => 'role',
 *          // ACL rules, optional
 *          'access'        => array(),
 *          'config'        => array(
 *              'a' => array(
 *                  'title'         => 'Config A',
 *                  'description'   => 'Config A hint',
 *                  'edit'          => 'select',
 *                  'value'         => 'option_a',
 *                  'edit'          => array(
 *                      'attributes'    => array(
 *                          'type'      => 'select'
 *                          'options'   => array(
 *                              1   => 'Option 1',
 *                              2   => 'Option 2',
 *                              3   => 'Option 3',
 *                          ),
 *                      ),
 *                  ),
 *                  'filter'        => 'num_int',
 *              ),
 *              'b'  => array(
 *                  'title'         => 'Config B',
 *                  'description'   => 'Config B hint',
 *                  'edit'          => 'SpecifiedEditElement',
 *                  'filter'        => 'string',
 *                  'value'         => 'good',
 *              ),
 *              'c'  => array(
 *                  'title'         => 'Config C',
 *                  'description'   => 'Config C hint',
 *                  'edit'          => 'input', // optional
 *                  'filter'        => 'string',
 *                  'value'         => __('sample text'),
 *              ),
 *          ),
 *      ),
 *      ...
 *  );
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

class Block extends AbstractResource
{
    /** @var string */
    //protected $moduleSeperator = '-';

    /**
     * Canonize block data for creation
     *
     * @param array $block
     * @return array
     */
    protected function canonizeAdd($block)
    {
        $module = $this->event->getParam('module');
        $classPrefix = 'Module\\'
                     . ucfirst($this->event->getParam('directory'));
        if (is_array($block['render'])) {
            $block['render'] = implode('::', $block['render']);
        }
        $block['render'] = $classPrefix . '\\' . ucfirst($block['render']);

        $data = array(
            'name'          => $block['name'],
            'title'         => $block['title'],
            'description'   => isset($block['description'])
                                ? $block['description'] : '',

            'module'        => $module,
            'render'        => $block['render'],
            'template'      => isset($block['template'])
                                ? $block['template'] : '',
            'config'        => isset($block['config'])
                                ? $block['config'] : array(),
            'cache_level'   => isset($block['cache_level'])
                                ? $block['cache_level'] : '',
            'access'        => isset($block['access'])
                                ? $block['access'] : array(),
        );

        return $data;
    }

    /**
     * Canonize block data for update
     *
     * @param array $block
     * @return array
     */
    protected function canonizeUpdate($block)
    {
        $module = $this->event->getParam('module');
        $classPrefix = 'Module\\'
                     . ucfirst($this->event->getParam('directory'));
        if (is_array($block['render'])) {
            $block['render'] = implode('::', $block['render']);
        }
        $block['render'] = $classPrefix . '\\' . ucfirst($block['render']);

        $data = array(
            'title'         => $block['title'],
            'description'   => isset($block['description'])
                                ? $block['description'] : '',

            'render'        => $block['render'],
            'template'      => isset($block['template'])
                                ? $block['template'] : '',
            'config'        => isset($block['config'])
                                ? $block['config'] : array(),
            'cache_level'   => isset($block['cache_level'])
                                ? $block['cache_level'] : '',
            'access'        => isset($block['access'])
                                ? $block['access'] : array(),

            //'link'          => isset($block['link']) ? $block['link'] : '',
            //'class'         => isset($block['class']) ? $block['class'] : '',
        );

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        if (empty($this->config)) {
            return;
        }
        $module = $this->event->getParam('module');
        $blocks = $this->config;
        foreach ($blocks as $key => $block) {
            // break the loop if missing block config
            if (empty($block['render'])) {
                continue;
            }
            $block['name'] = $key;
            $block['module'] = $module;
            $data = $this->canonizeAdd($block);
            $message = array();
            $status = $this->addBlock($data, $message);
            if (!$status) {
                $message[] = sprintf('Block "%s" is not created.', $key);
                return array(
                    'status'    => false,
                    'message'   => $message,
                );
            }
        }

        Pi::registry('block')->clear($module);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('block')->clear($module);

        if ($this->skipUpgrade()) {
            return;
        }

        $blocks = $this->config ?: array();

        $model = Pi::model('block_root');
        foreach ($blocks as $key => $block) {
            // break the loop if missing block config
            if (empty($block['render'])) {
                continue;
            }
            $block['name'] = $key;
            $block['module'] = $module;
            $rowset = $model->select(array(
                'name'      => $key,
                'module'    => $module,
            ));
            // Add new block
            if (!$rowset->count()) {
                $data = $this->canonizeAdd($block);
                $message = array();
                $status = $this->addBlock($data, $message);
                if (!$status) {
                    $message[] = sprintf('Block "%s" is not created.', $key);
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                }
            // Update existent block
            } else {
                $row = $rowset->current();
                $data = $this->canonizeUpdate($block);
                $message = array();
                $status = $this->updateBlock($row, $data, $message);
                if (!$status) {
                    $message[] = sprintf('Block "%s" is not updated.', $key);
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                }
            }
        }

        // Remove deprecated blocks
        $rowset = $model->select(array('module' => $module));
        foreach ($rowset as $row) {
            if (!isset($blocks[$row->name])) {
                $message = array();
                $status = $this->deleteBlock($row, $message);
                if (!$status) {
                    $message[] = sprintf(
                        'Deprecated block "%s" is not updated.',
                        $row->key
                    );
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                }
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');

        Pi::model('block')->delete(array('module' => $module));
        Pi::model('block_root')->delete(array('module' => $module));
        Pi::registry('block')->clear($module);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        $module = $this->event->getParam('module');
        Pi::model('block')->update(
            array('active' => 1),
            array('module' => $module)
        );

        Pi::registry('block')->clear($module);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        $module = $this->event->getParam('module');
        Pi::model('block')->update(
            array('active' => 0),
            array('module' => $module)
        );

        Pi::registry('block')->clear($module);

        return true;
    }

    /**
     * Adds a block and its relevant options, ACL rules
     *
     * @param array $block
     * @param string $message
     * @return bool
     */
    protected function addBlock($block, &$message)
    {
        $result = Pi::api('system', 'block')->add($block);

        return $result['status'];
    }

    /**
     * Updates a block and its relevant options
     *
     * @param RootRow $rootRow
     * @param array $block
     * @param array $message
     * @return bool
     */
    protected function updateBlock(RootRow $rootRow, $block, &$message)
    {
        $result = Pi::api('system', 'block')->update($rootRow, $block);

        return $result['status'];
    }

    /**
     * Deletes a block root and its relevant views, ACL rules
     *
     * @param RootRow $rootRow
     * @param array $message
     * @return bool
     */
    protected function deleteBlock(RootRow $rootRow, &$message)
    {
        $result = Pi::api('system', 'block')->delete($rootRow, true);

        return $result['status'];
    }
}
