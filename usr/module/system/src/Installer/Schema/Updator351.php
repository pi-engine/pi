<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace   Module\System\Installer\Schema;

use Pi;
use Pi\Application\Installer\Schema\AbstractUpdator;
use Pi\Application\Installer\Resource\Config as ConfigResource;

/**
 * System schema update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Updator351 extends AbstractUpdator
{
    /**
     * Update system table schema
     *
     * @param string $version
     *
     * @return bool
     */
    public function upgrade($version)
    {
        if (version_compare($version, '3.5.0', '<')) {
            $updator = new Updator350($this->handler);
            $result = $updator->upgrade($version);
            if (false === $result) {
                return $result;
            }
        }
        $result = $this->from350($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from350($version)
    {
        $status = true;

        if (version_compare($version, '3.5.1', '<')) {
            // Update head meta category
            $modelCategory = Pi::model('config_category');
            $metaCategory = $modelCategory->find('meta', 'name');
            if ($metaCategory) {
                $metaCategory->save(array('name' => 'head_meta'));
            }

            // Update meta item category
            $modelItem = Pi::model('config');
            $modelItem->update(array('category' => 'head_meta'), array('module' => 'system', 'category' => 'meta'));

            $modules = Pi::registry('module')->read();
            $e = $this->handler->getEvent();
            $curModule = $e->getParam('module');
            foreach (array_keys($modules) as $module) {
                $options = Pi::service('module')->loadMeta($module, 'config', true);
                $resourceHandler = new configResource($options);
                $e->setParam('module', $module);
                $resourceHandler->setEvent($e);
                $resourceHandler->update();
                Pi::registry('config')->clear($module);
            }
            $e->setParam('module', $curModule);
        }

        return $status;
    }
}
