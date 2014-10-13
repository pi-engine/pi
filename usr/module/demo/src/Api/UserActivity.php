<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Api;

use Module\User\Api\AbstractActivityCallback;

class UserActivity extends AbstractActivityCallback
{
    /**
     * {@inheritDoc}
     */
    public function get($uid, $limit, $offset = 0)
    {
        $data = array();
        for ($i = 0; $i < $limit; $i++) {
            $message = sprintf('Uid %d activity item #%d.', $uid, $i);
            $data['items'][] = array(
                'message' => $message,
                'time'    => time() - $i * 3600,
            );
        }

        return $data;
    }
}
