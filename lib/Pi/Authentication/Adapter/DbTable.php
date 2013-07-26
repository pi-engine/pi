<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Authentication\Adapter;

use Pi;
use Pi\Application\Model\User\Account as AccountModel;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result as AuthenticationResult;

/**
 * Db Authentication Adapter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DbTable implements AdapterInterface
{
    /**
     * Table name to check
     *
     * @var string
     */
    protected $tableName;

    /**
     * User account table
     *
     * @var AccountModel
     */
    protected $table;

    /** @var string Identity value */
    protected $identity = null;

    /** @var string Credential value */
    protected $credential = null;

    /** @var array Result meta */
    protected $authenticateResultInfo = null;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->tableName = isset($options['table_name']) ? $options['table_name'] : 'user_account';
    }

    /**
     * Set the table name to be used in the select query
     *
     * @param  string $tableName
     * @return $this
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Set the value to be used as the identity
     *
     * @param  string $value
     * @return $this
     */
    public function setIdentity($value)
    {
        $this->identity = $value;
        return $this;
    }

    /**
     * Set the credential value to be used, optionally can specify a treatment
     * to be used, should be supplied in parameterized form, such as 'MD5(?)' or 'PASSWORD(?)'
     *
     * @param  string $credential
     * @return $this
     */
    public function setCredential($credential)
    {
        $this->credential = $credential;
        return $this;
    }

    /**
     * Authenticate a user
     *
     * This method is called to attempt an authentication.
     * Previous to this call, this adapter would have already been configured with all necessary information to successfully connect to a database
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
     * Validate authentication
     *
     * This method attempts to validate that the record in the resultset is indeed a record that matched the
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
     * Create authentication result
     *
     * Creates a Zend\Authentication\Result object from
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
