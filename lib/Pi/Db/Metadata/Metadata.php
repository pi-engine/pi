<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Pi\Db\Metadata;

use Zend\Db\Metadata\Metadata as ZendMetadata;
use Zend\Db\Adapter\Adapter;

class Metadata extends ZendMetadata
{
    /**
     * {@inheritDoc}
     */
    protected function createSourceFromAdapter(Adapter $adapter)
    {
        switch ($adapter->getPlatform()->getName()) {
            case 'MySQL':
                return new Source\MysqlMetadata($adapter);
            case 'SQLServer':
                return new Source\SqlServerMetadata($adapter);
            case 'SQLite':
                return new Source\SqliteMetadata($adapter);
            case 'PostgreSQL':
                return new Source\PostgresqlMetadata($adapter);
        }

        throw new \Exception('cannot create source from adapter');
    }
}
