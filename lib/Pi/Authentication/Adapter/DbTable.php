<?php
/**
 * Db Authentication Adapter
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\Authentication
 * @subpackage      Adpater
 * @version         $Id$
 */

namespace Pi\Authentication\Adapter;

use Pi;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result as AuthenticationResult;

class DbTable implements AdapterInterface
{
    /**
     * $tableName - the table name to check
     *
     * @var string
     */
    protected $tableName;

    protected $table;

    /**
     * $identity - Identity value
     *
     * @var string
     */
    protected $identity = null;

    /**
     * $credential - Credential values
     *
     * @var string
     */
    protected $credential = null;

    /**
     * $authenticateResultInfo
     *
     * @var array
     */
    protected $authenticateResultInfo = null;

    public function __construct($options = array())
    {
        $this->tableName = isset($options['table_name']) ? $options['table_name'] : 'user_account';
    }

    /**
     * setTableName() - set the table name to be used in the select query
     *
     * @param  string $tableName
     * @return Db Provides a fluent interface
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * setIdentity() - set the value to be used as the identity
     *
     * @param  string $value
     * @return Db Provides a fluent interface
     */
    public function setIdentity($value)
    {
        $this->identity = $value;
        return $this;
    }

    /**
     * setCredential() - set the credential value to be used, optionally can specify a treatment
     * to be used, should be supplied in parameterized form, such as 'MD5(?)' or 'PASSWORD(?)'
     *
     * @param  string $credential
     * @return Db Provides a fluent interface
     */
    public function setCredential($credential)
    {
        $this->credential = $credential;
        return $this;
    }

    /**
     * authenticate() - This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @return AuthenticationResult
     */
    public function authenticate()
    {
        $this->authenticateResultInfo = array(
            'code'     => AuthenticationResult::FAILURE,
            'identity' => $this->identity,
            'messages' => array()
        );

        $this->table = Pi::model($this->tableName);
        $resultIdentities = $this->table->select(array($this->table->getIdentityColumn() => $this->identity));

        if (count($resultIdentities) < 1) {
            $this->authenticateResultInfo['code'] = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
            $authResult = $this->authenticateCreateAuthResult();
        } elseif (count($resultIdentities) > 1) {
            $this->authenticateResultInfo['code'] = AuthenticationResult::FAILURE_IDENTITY_AMBIGUOUS;
            $this->authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
            $authResult = $this->authenticateCreateAuthResult();
        } else {
            // Check and break on success.
            foreach ($resultIdentities as $identity) {
                $authResult = $this->authenticateValidateResult($identity);
                if ($authResult->isValid()) {
                    break;
                }
            }
        }

        return $authResult;
    }


    /**
     * _authenticateValidateResult() - This method attempts to validate that
     * the record in the resultset is indeed a record that matched the
     * identity provided to this adapter.
     *
     * @param  array $resultIdentity
     * @return AuthenticationResult
     */
    protected function authenticateValidateResult($resultIdentity)
    {
        if ($resultIdentity->transformCredential($this->credential) != $resultIdentity->getCredential()) {
            $this->authenticateResultInfo['code'] = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->authenticateCreateAuthResult();
        }
        $this->resultRow = $resultIdentity;

        $this->authenticateResultInfo['code'] = AuthenticationResult::SUCCESS;
        $this->authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->authenticateCreateAuthResult();
    }

    /**
     * _authenticateCreateAuthResult() - Creates a Zend_Auth_Result object from
     * the information that has been collected during the authenticate() attempt.
     *
     * @return AuthenticationResult
     */
    protected function authenticateCreateAuthResult()
    {
        return new AuthenticationResult(
            $this->authenticateResultInfo['code'],
            $this->authenticateResultInfo['identity'],
            $this->authenticateResultInfo['messages']
        );
    }
}
