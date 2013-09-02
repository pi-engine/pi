<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Authentication\Adapter\DbTable;

use Zend\Authentication\Result as AuthenticationResult;

/**
 * Pi authentication db table callback adapter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @see \Zend\Authentication\DbTable\AbstractAdapter
 */
class CallbackAdapter extends AbstractAdapter
{
    /**
     * For validation to happen in code
     *
     * @var callable
     */
    protected $callback = null;

    /**
     * Set callback for credential validation
     *
     * @param Callable $callback
     * @return void
     */
    public function setCallback($callback)
    {
        if (is_callable($callback)) {
            $this->callback = $callback;
        }
    }

    /**
     * Get callback for credential validation
     *
     * @return Callable
     */
    protected function getCallback()
    {
        if (!$this->callback || !is_callable($this->callback)) {
            $this->callback = function($a, $b, $identity) {
                return $a === md5($b);
            };
        }

        return $this->callback;
    }

    /**
     * {@inheritDoc}
     */
    protected function authenticateValidateResult($resultIdentity)
    {
        try {
            $callbackResult = call_user_func(
                $this->getCallback(),
                $resultIdentity[$this->credentialColumn],
                $this->credential,
                $resultIdentity
            );
        } catch (\Exception $e) {
            $this->authenticateResultInfo['code']
                = AuthenticationResult::FAILURE_UNCATEGORIZED;
            $this->authenticateResultInfo['messages'][]
                = $e->getMessage();
            return $this->authenticateCreateAuthResult();
        }
        if ($callbackResult !== true) {
            $this->authenticateResultInfo['code']
                = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->authenticateResultInfo['messages'][]
                = __('Supplied credential is invalid.');
            return $this->authenticateCreateAuthResult();
        }

        $this->setResultRow($resultIdentity);

        $this->authenticateResultInfo['code']
            = AuthenticationResult::SUCCESS;
        $this->authenticateResultInfo['messages'][]
            = __('Authentication successful.');

        return $this->authenticateCreateAuthResult();
    }

}
