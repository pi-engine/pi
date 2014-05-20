<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

/**
 * Database connection bootstrap
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Database extends AbstractResource
{
    /**
     * {@inheritDoc}
     * @return Pi\Application\Db
     */
    public function boot()
    {
        try {
            Pi::service('database')->connect();
        } catch (\Exception $e) {
            pi::service('log')->mute();
            $exceptionMessage = preg_replace(
                '/user ([^\s]+) to database ([^\s]+)/',
                'user to database',
                $e->getMessage()
            );
            $message = '<h1>' . 'Database connection is failed.' . '</h1>'
                . '<p>' . $exceptionMessage . '</p>';
            echo $message;

            exit();
        }

        $db = Pi::service('database')->db($this->options);

        return $db;
    }
}
