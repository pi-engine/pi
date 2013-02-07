<?PHP
/**
 * Pi Db statement class
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
 * @package         Pi\Db
 * @subpackage      RowGateway
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Db\Adapter\Driver;

use PDO;
use PDOStatement;
//use Pi\Application\Db;
use Pi\Log\DbProfiler;

class Statement extends PDOStatement
{
    /**
     * DB profiling logger
     * @var DbProfiler
     */
    protected $profiler;

    /**
     * DB query counter
     *
     * @var int
     */
    protected $counter = 0;

    protected $parameters = array();

    /**
     * Constructor
     *
     * @param DbProfiler $profiler
     */
    protected function __construct($profiler = null)
    {
        $this->profiler = $profiler;
    }

    /**
     * Execute query with args and log query information
     *
     * @param array $args
     * @return boolean
     */
    public function execute($args = null)
    {
        // Profiling starts
        if ($this->profiler) {
            $this->counter ++;
            $start = microtime(true);
        }
        $exception = null;
        try {
            if (null !== $args) {
                $status = parent::execute($args);
            } else {
                $status = parent::execute();
            }
        } catch (\Exception $e) {
            $status = false;
            $exception = $e;
        }

        //Profiling ends
        if ($this->profiler) {
            $message = '';
            if (!$status) {
                $errorInfo = $this->errorInfo();
                $message = $errorInfo[2];
            }
            $parameters = array_merge($this->parameters, (array) $args);
            // Write to log container
            $this->profiler->log(array(
                'start'         => $start,
                'elapse'        => microtime(true) - $start,
                'sql'           => $this->queryString,
                'parameters'    => $parameters,
                'message'       => $message,
                'status'        => $status,
            ));
        }

        if ($exception) {
            throw $exception;
        }

        return $status;
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
    {
        $result = parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
        if ($this->profiler) {
            $this->parameters[$parameter] = $variable;
        }
        return $result;
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        $result = parent::bindValue($parameter, $value, $data_type);
        if ($this->profiler) {
            $this->parameters[$parameter] = $value;
        }
        return $result;
    }
}
