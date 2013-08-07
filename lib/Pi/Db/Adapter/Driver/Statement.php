<?PHP
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Db\Adapter\Driver;

use PDO;
use PDOStatement;
use Pi\Log\DbProfiler;

/**
 * Pi DB custom statement class
 *
 * @see http://www.php.net/manual/en/pdo.setattribute.php
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Statement extends PDOStatement
{
    /**
     * DB profiling logger
     *
     * @var DbProfiler
     */
    protected $profiler;

    /**
     * DB query counter
     *
     * @var int
     */
    protected $counter = 0;

    /** @var array Bound parameters */
    protected $parameters = array();

    /**
     * Constructor
     *
     * @param DbProfiler $profiler
     */
    protected function __construct(DbProfiler $profiler = null)
    {
        $this->profiler = $profiler;
    }

    /**
     * Execute query with args and log query information
     *
     * @param array|null $args
     * @return bool
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

    /**
     * {@inheritDoc}
     */
    public function bindParam(
        $parameter,
        &$variable,
        $data_type = PDO::PARAM_STR,
        $length = null,
        $driver_options = null
    ) {
        $result = parent::bindParam(
            $parameter,
            $variable,
            $data_type,
            $length,
            $driver_options
        );
        if ($this->profiler) {
            $this->parameters[$parameter] = $variable;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        $result = parent::bindValue($parameter, $value, $data_type);
        if ($this->profiler) {
            $this->parameters[$parameter] = $value;
        }
        return $result;
    }
}
