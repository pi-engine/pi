<?php
/**
 * Pi DB Query Profiler
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
 * @package         Pi\Log
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Log;

use Zend\Log\Writer\WriterInterface;
use Zend\Stdlib\SplPriorityQueue;

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
     */
    public function __construct($options = array())
    {
        $this->writers = new SplPriorityQueue();
    }

    /**
     * Shutdown all writers
     *
     * @return void
     */
    public function shutdown()
    {
        foreach ($this->writers->toArray() as $writer) {
            try {
                $writer->shutdown();
            } catch (\Exception $e) {}
        }
    }

    /**
     * Add a writer to a logger
     *
     * @param WriterInterface $writer
     * @param int $priority
     * @return Profiler
     */
    public function addWriter(WriterInterface $writer, $priority = 1)
    {
        $this->writers->insert($writer, $priority);
        return $this;
    }

    /**
     * Write query profiler info
     *
     * @param array $info
     * @return DbProfiler
     */
    public function log(array $info)
    {
        foreach ($this->writers->toArray() as $writer) {
            $writer->doDb($info);
        }
        return $this;
    }
}
