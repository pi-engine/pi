<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Authentication\Adapter\DbTable;

use Pi;
use Pi\Authentication\Adapter\AbstractAdapter as BaseAbstractAdapter;
use Pi\Authentication\Result as AuthenticationResult;
use Zend\Db\RowGateway\AbstractRowGateway;
use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Pi authentication db table abstract adapter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @see \Zend\Authentication\DbTable\AbstractAdapter
 */
abstract class AbstractAdapter extends BaseAbstractAdapter implements
    AdapterInterface
{
    /**
     * Database Connection
     *
     * @var DbAdapter
     */
    protected $dbAdapter = null;

    /**
     * Table name to check
     *
     * @var string|array
     */
    protected $tableName;

    /**
     * Column to use as the identity
     *
     * @var string
     */
    protected $identityColumn = null;

    /**
     * Column to be used as the credentials
     *
     * @var string
     */
    protected $credentialColumn = null;

    /**
     * $authenticateResultInfo
     *
     * @var array
     */
    protected $authenticateResultInfo = null;

    /**
     * Flag to indicate same Identity can be used with different credentials.
     * Default is FALSE and need to be set to true to allow ambiguity usage.
     *
     * @var bool
     */
    protected $ambiguityIdentity = false;

    /**
     * {@inheritDoc}
     */
    public function setDbAdapter(DbAdapter $adapter = null)
    {
        $this->dbAdapter = $adapter;
    }

    /**
     * Set the table name to be used in the select query
     *
     * @param  string $tableName
     * @return self
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Set the column name to be used as the identity column
     *
     * @param  string $identityColumn
     * @return self
     */
    public function setIdentityColumn($identityColumn)
    {
        $this->identityColumn = $identityColumn;

        return $this;
    }

    /**
     * Set the column name to be used as the credential column
     *
     * @param  string $credentialColumn
     * @return self
     */
    public function setCredentialColumn($credentialColumn)
    {
        $this->credentialColumn = $credentialColumn;

        return $this;
    }

    /**
     * Sets a flag for usage of identical identities
     * with unique credentials. It accepts integers (0, 1) or boolean (true,
     * false) parameters. Default is false.
     *
     * @param  int|bool $flag
     * @return self
     */
    public function setAmbiguityIdentity($flag)
    {
        if (is_int($flag)) {
            $this->ambiguityIdentity = (1 === $flag ? true : false);
        } elseif (is_bool($flag)) {
            $this->ambiguityIdentity = $flag;
        }

        return $this;
    }

    /**
     * Returns TRUE for usage of multiple identical
     * identities with different credentials, FALSE if not used.
     *
     * @return bool
     */
    public function getAmbiguityIdentity()
    {
        return $this->ambiguityIdentity;
    }

    /**
     * _authenticateSetup() - This method abstracts the steps involved with
     * making sure that this adapter was indeed setup properly with all
     * required pieces of information.
     *
     * @throws \RuntimeException in the event that setup was not done properly
     * @return bool
     */
    protected function authenticateSetup()
    {
        $exception = null;

        if ($this->tableName == '') {
            $exception = __('A table must be supplied for the DbTable authentication adapter.');
        } elseif ($this->identityColumn == '') {
            $exception = __('An identity column must be supplied for the DbTable authentication adapter.');
        } elseif ($this->credentialColumn == '') {
            $exception = __('A credential column must be supplied for the DbTable authentication adapter.');
        } elseif ($this->identity == '') {
            $exception = __('A value for the identity was not provided prior to authentication with DbTable.');
        } elseif ($this->credential === null) {
            $exception = __('A credential value was not provided prior to authentication with DbTable.');
        }

        if (null !== $exception) {
            throw new \RuntimeException($exception);
        }

        $this->authenticateResultInfo = array(
            'code'      => AuthenticationResult::FAILURE,
            'identity'  => $this->identity,
            'messages'  => array(),
            'data'      => array(),
        );

        return true;
    }

    /**
     * This method attempts to make
     * certain that only one record was returned in the resultset
     *
     * @param  array $resultIdentities
     * @return bool|AuthenticationResult
     */
    protected function authenticateValidateResultSet(array $resultIdentities)
    {
        if (count($resultIdentities) < 1) {
            $this->authenticateResultInfo['code']
                = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticateResultInfo['messages'][]
                = __('A record with the supplied identity could not be found.');
            return $this->authenticateCreateAuthResult();
        } elseif (count($resultIdentities) > 1
            && false === $this->getAmbiguityIdentity()) {
            $this->authenticateResultInfo['code']
                = AuthenticationResult::FAILURE_IDENTITY_AMBIGUOUS;
            $this->authenticateResultInfo['messages'][]
                = __('More than one record matches the supplied identity.');
            return $this->authenticateCreateAuthResult();
        }

        return true;
    }

    /**
     * Creates a AuthenticationResult object from the information that
     * has been collected during the authenticate() attempt.
     *
     * @return AuthenticationResult
     */
    protected function authenticateCreateAuthResult()
    {
        return new AuthenticationResult($this->authenticateResultInfo);
    }

    /**
     * This method is called to attempt an authentication. Previous to this
     * call, this adapter would have already been configured with all
     * necessary information to successfully connect to a database table and
     * attempt to find a record matching the provided identity.
     *
     * @return AuthenticationResult
     */
    public function authenticate()
    {
        $this->authenticateSetup();

        $resultIdentities = $this->getQueryResult();
        $authResult = $this->authenticateValidateResultSet($resultIdentities);
        if ($authResult instanceof AuthenticationResult) {
            return $authResult;
        }

        // At this point, ambiguity is already done.
        // Loop, check and break on success.
        foreach ($resultIdentities as $identity) {
            $authResult = $this->authenticateValidateResult($identity);
            if ($authResult->isValid()) {
                break;
            }
        }

        return $authResult;
    }

    /**
     * Return query result
     *
     * @return array
     */
    protected function getQueryResult()
    {
        $options = array();
        if ($this->dbAdapter instanceof DbAdapter) {
            $options['adapter'] = $this->dbAdapter;
        }
        if (is_array($this->tableName)) {
            list($modelName, $module) = $this->tableName;
        } else {
            list($modelName, $module) = array($this->tableName, '');
        }
        $model = Pi::model($modelName, $module, $options);
        $rowset = $model->select(array(
            $this->identityColumn => $this->identity
        ));
        $resultIdentities = array();
        foreach($rowset as $row) {
            $resultIdentities[] = $row;
        }

        return $resultIdentities;
    }

    /**
     * Validate that the record in the resultset is indeed a record
     * that matched the identity provided to this adapter.
     *
     * @param  array $resultIdentity
     * @return AuthenticationResult
     */
    abstract protected function authenticateValidateResult($resultIdentity);

    /**
     * {@inheritDoc}
     */
    public function setResultRow($resultRow = array())
    {
        if ($resultRow instanceof AbstractRowGateway) {
            $this->resultRow = $resultRow->toArray();
        } else {
            $this->resultRow = (array) $resultRow;
        }

        return $this;
    }

}
