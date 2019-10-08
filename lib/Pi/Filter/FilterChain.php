<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Filter;

use Zend\Filter\FilterChain as ZendFilterChain;

/**
 * Filter chain with specified plugin manager
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FilterChain extends ZendFilterChain
{
    /**
     * {@inheritDoc}
     */
    public function getPluginManager()
    {
        if (!$this->plugins) {
            $this->setPluginManager(new FilterPluginManager());
        }

        return $this->plugins;
    }
}
