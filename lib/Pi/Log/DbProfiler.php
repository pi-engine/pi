<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Log;

use Laminas\Log\Writer\WriterInterface;
use Laminas\Stdlib\SplPriorityQueue;

/**
 * Database query profiler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DbProfiler
{
    /**
     * Writers
     *
     * @var SplPriorityQueue
     */
    protected $writers;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->writers = new SplPriorityQueue();
    }

    /**
     * Shutdown all writers
     *
     * Write log messages to corresponding storages
     *
     * @return void
     */
    public function shutdown()
    {
        foreach ($this->writers->toArray() as $writer) {
            try {
                $writer->shutdown();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Add a writer to a logger
     *
     * @param WriterInterface $writer
     * @param int             $priority
     *
     * @return self
     */
    public function addWriter(WriterInterface $writer, $priority = 1)
    {
        $this->writers->insert($writer, $priority);

        return $this;
    }

    /**
     * Register query profiler info
     *
     * @param array $info
     *
     * @return self
     */
    public function log(array $info)
    {
        foreach ($this->writers->toArray() as $writer) {
            $writer->doDb($info);
        }

        return $this;
    }
}
