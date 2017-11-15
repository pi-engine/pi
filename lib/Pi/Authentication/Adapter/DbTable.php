<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
