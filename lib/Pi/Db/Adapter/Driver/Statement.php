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

use PDOStatement;
use Pi\Application\Db;
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
        $exception = '';
        try {
            if (null !== $args) {
                $status = parent::execute($args);
            } else {
                $status = parent::execute();
            }
        } catch (\Exception $e) {
            $status = false;
            //$exception = $e->getMessage();
            throw $e;
        }

        //Profiling ends
        if ($this->profiler) {
            $timer = microtime(true) - $start;
            //$message = sprintf('[%s]: %s', $status ? 'rows:' . $this->rowCount() : 'failed', $this->queryString);
            // Write to log container
            $this->profiler->log(array(
                'timestamp' => $start,
                'message'   => $exception,
                'query'     => $this->queryString,
                'status'    => $status,
                'timer'     => $timer,
                'count'     => $this->rowCount(),
            ));
        // Trigger error if profiler is not enabled
        } elseif ($exception) {
            trigger_error($exception, E_USER_ERROR);
        }

        return $status;
    }
}
