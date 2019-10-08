<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Authentication\Adapter\DbTable;

/**
 * Pi authentication db table credential treatment adapter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @see \Zend\Authentication\DbTable\AbstractAdapter
 */
class CredentialTreatmentAdapter extends AbstractAdapter
{
    /**
     * {@inheritDoc}
     */
    protected function authenticateValidateResult($resultIdentity)
    {
        throw new \Exception('Not implemented yet.');
    }
}
