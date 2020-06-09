<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Db\Adapter;

use Laminas\Db\Adapter\Adapter as ZendAdapter;
use Laminas\Db\Adapter\Driver;
use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Db\Exception;
use Laminas\Db\ResultSet;

/**
 * {@inheritDoc}
 */
class Adapter extends ZendAdapter
{
    /**
     * {@inheritDoc}
     */
    public function query(
        $sql,
        $parametersOrQueryMode = self::QUERY_MODE_PREPARE,
        ResultSet\ResultSetInterface $resultPrototype = null
    )
    {
        if (is_string($parametersOrQueryMode) && in_array($parametersOrQueryMode, [self::QUERY_MODE_PREPARE, self::QUERY_MODE_EXECUTE])) {
            $mode       = $parametersOrQueryMode;
            $parameters = null;
        } elseif (is_array($parametersOrQueryMode) || $parametersOrQueryMode instanceof ParameterContainer) {
            $mode       = self::QUERY_MODE_PREPARE;
            $parameters = $parametersOrQueryMode;
        } else {
            throw new Exception\InvalidArgumentException('Parameter 2 to this method must be a flag, an array, or ParameterContainer');
        }

        if ($mode == self::QUERY_MODE_PREPARE) {
            $this->lastPreparedStatement = null;
            $this->lastPreparedStatement = $this->driver->createStatement($sql);
            $this->lastPreparedStatement->prepare();
            if (is_array($parameters) || $parameters instanceof ParameterContainer) {
                $this->lastPreparedStatement->setParameterContainer((is_array($parameters)) ? new ParameterContainer($parameters) : $parameters);
                $result = $this->lastPreparedStatement->execute();
            } else {
                return $this->lastPreparedStatement;
            }
        } else {
            /**#@+ Pi Engine
             * Skip PDO direct SQL query and proxy to PDOStatement
             * so that all queries will be logged for debug and profiling
             * Modified by Taiwen Jiang
             */
            //$result = $this->driver->getConnection()->execute($sql);
            $statement = $this->driver->getConnection()->connect()
                ->getResource()->prepare($sql);
            $statement->execute();
            $result = $this->driver->createResult($statement);
            /**#@-*/
        }

        if ($result instanceof Driver\ResultInterface && $result->isQueryResult()) {
            $resultSet = clone ($resultPrototype ?: $this->queryResultSetPrototype);
            $resultSet->initialize($result);
            return $resultSet;
        }

        return $result;
    }
}
