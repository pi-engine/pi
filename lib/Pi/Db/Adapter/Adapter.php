<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Db\Adapter;

use Zend\Db\Adapter\Adapter as ZendAdapter;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Driver;
use Zend\Db\ResultSet;
use Zend\Db\Exception;

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
        $parametersOrQueryMode = self::QUERY_MODE_PREPARE
    ) {
        if (is_string($parametersOrQueryMode)
            && in_array(
                $parametersOrQueryMode,
                array(self::QUERY_MODE_PREPARE, self::QUERY_MODE_EXECUTE)
              )
        ) {
            $mode = $parametersOrQueryMode;
            $parameters = null;
        } elseif (is_array($parametersOrQueryMode)
            || $parametersOrQueryMode instanceof ParameterContainer
        ) {
            $mode = self::QUERY_MODE_PREPARE;
            $parameters = $parametersOrQueryMode;
        } else {
            throw new Exception\InvalidArgumentException(
                'Parameter 2 to this method must be a flag,'
                . ' an array, or ParameterContainer'
            );
        }

        if ($mode == self::QUERY_MODE_PREPARE) {
            $this->lastPreparedStatement = null;
            $this->lastPreparedStatement =
                $this->driver->createStatement($sql);
            $this->lastPreparedStatement->prepare();
            if (is_array($parameters)
                || $parameters instanceof ParameterContainer
            ) {
                $this->lastPreparedStatement
                    ->setParameterContainer((is_array($parameters))
                        ? new ParameterContainer($parameters) : $parameters);
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
            $result = $this->driver->getConnection()->connect()
                ->getResource()->prepare($sql)->execute();
            /**#@-*/
        }

        if ($result instanceof Driver\ResultInterface
            && $result->isQueryResult()
        ) {
            $resultSet = clone $this->queryResultSetPrototype;
            $resultSet->initialize($result);
            return $resultSet;
        }

        return $result;
    }
}
