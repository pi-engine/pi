<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * System Event Handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Event extends AbstractApi
{
    /**
     * Module installation
     *
     * @param string $data
     */
    public function moduleinstall($data)
    {
        $model = Pi::model('update', $this->module);
        $data = array(
            'title'     => sprintf(__('Module `%s` installed'), $data),
            'content'   => sprintf(
                __('The module `%s` is installed successfully.'),
                $data
            ),
            'uri'       => Pi::url('www', true),
            'time'      => time(),
        );
        $model->insert($data);
    }

    /**
     * Module uninstallation
     *
     * @param string $data
     */
    public function moduleuninstall($data)
    {
        $model = Pi::model('update', $this->module);
        $data = array(
            'title'     => sprintf(__('Module `%s` uninstalled'), $data),
            'content'   => sprintf(
                __('The module `%s` is uninstalled successfully.'),
                $data
            ),
            'uri'       => Pi::url('www', true),
            'time'      => time(),
        );
        $model->insert($data);
    }

    /**
     * Module update
     *
     * @param string $data
     */
    public function moduleupdate($data)
    {
        $model = Pi::model('update', $this->module);
        $row = array(
            'title'     => sprintf(__('Module `%s` updated'), $data),
            'content'   => sprintf(
                __('The module `%s` is updated successfully.'),
                $data
            ),
            'uri'       => Pi::url('www', true),
            'time'      => time(),
        );
        $model->insert($row);
    }

    /**
     * Module activation
     *
     * @param string $data
     */
    public function moduleactivate($data)
    {
        $model = Pi::model('update', $this->module);
        $data = array(
            'title'     => sprintf(__('Module `%s` activated'), $data),
            'content'   => sprintf(
                __('The module `%s` is activated successfully.'),
                $data
            ),
            'uri'       => Pi::url('www', true),
            'time'      => time(),
        );
        $model->insert($data);
    }

    /**
     * Module deactivation
     *
     * @param string $data
     */
    public function moduledeactivate($data)
    {
        $model = Pi::model('update', $this->module);
        $data = array(
            'title'     => sprintf(__('Module %s deactivated'), $data),
            'content'   => sprintf(
                __('The module %s is deactivated successfully.'),
                $data
            ),
            'uri'       => Pi::url('www', true),
            'time'      => time(),
        );
        $model->insert($data);
    }
}
