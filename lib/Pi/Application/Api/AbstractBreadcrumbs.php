<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Api;

/**
 * Abstract class for module breadcrumbs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractBreadcrumbs extends AbstractApi
{
    /**
     * Load module breadcrumbs data
     *
     * @return array Array of associative label and href
     */
    public function __invoke()
    {
        return $this->load();
    }

    /**
     * Load module breadcrumbs data
     *
     * @return array Array of associative label and href
     */
    abstract public function load();
}
