<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Media\Dispatch;

use Pi;

class Ftp extends AbstractDispatch
{
    /**
     * Ftp connection data
     * 
     * @var array
     */
    protected $connection = array();
    
    /**
     * Constructor, set connection
     * 
     * @param array $options
     */
    public function __construct($configs, $options = array())
    {
        if (isset($configs['connection'])) {
            $this->setConnection($configs['connection']);
        }
        parent::__construct($configs, $options);
    }
    
    /**
     * Set ftp connection parameters
     * 
     * @param array $connection
     */
    public function setConnection($connection)
    {
        $columns = array('server', 'username', 'password', 'port');
        foreach (array_keys($connection) as $col) {
            if (!in_array($col, $columns)) {
                $message = sprintf('Field %s is needed', $col);
                throw new \Exception($message);
            }
        }
        $this->connection = $connection;
        
        return $this;
    }
    
    /**
     * Upload file by ftp
     * 
     * @param string $source
     * @param string $target
     * @return boolean
     * @throws \Exception 
     */
    public function copy($source, $target)
    {
        if (empty($this->connection)) {
            throw new \Exception('Connection parameters missed');
        }
        $params = $this->connection;
        if ($params['port']) {
            $conn = ftp_connect($params['server'], (int) $params['port']);
        } else {
            $conn = ftp_connect($params['server']);
        }
        if (!$conn) {
            throw new \Exception('Cannot connect to server');
        }
        if (!ftp_login($conn, $params['username'], $params['password'])) {
            throw new \Exception('Error username or password');
        }
        
        if (!ftp_put($conn, $target, $source, FTP_ASCII)) {
            $message = sprintf(
                'There was a problem while uploading %s',
                basename($source)
            );
            throw new \Exception($message);
        }
        
        ftp_close($conn);
        
        return true;
    }
}
