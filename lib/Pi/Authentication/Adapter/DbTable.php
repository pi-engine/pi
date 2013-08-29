<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Authentication\Adapter;


/**
 * Db Authentication Adapter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DbTable extends DbTable\CallbackAdapter
{
    /**
     * {@inheritDoc}
     */
    protected function getCallback()
    {
        if (!$this->callback) {
            /*
            $credentialColumn = $this->credentialColumn;
            $this->callback = function (
                $a,
                $b,
                $identity
            ) use ($credentialColumn) {
                return $a === $identity->transformCredential($b);
            };
            */
            $this->callback = function ($a, $b, $identity) {
                return $a === $identity->transformCredential($b);
            };
        }

        return $this->callback;
    }
}
