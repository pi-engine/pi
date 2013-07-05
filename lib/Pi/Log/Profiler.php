<?php
/**
 * Pi Profiler
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

class Profiler
{
    /**
     * List logs
     *
     * @var array
     */
    protected $timers = array();

    /**
     * Writers
     *
     * @var SplPriorityQueue
     */
    protected $writers;

    /**
     * Constructor
     *
     * @return Logger
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
        foreach (array_keys((array) $this->timers) as $name) {
            if ($name) {
                $this->write($name);
            }
        }
        foreach ($this->writers as $writer) {
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
     * Starts a timer
     * @param    string  $name   name of the timer
     * @return Profiler
     */
    public function start($name = 'PI')
    {
        if (!empty($this->timers[$name])) {
            $this->end($name);
        }
        $this->timers[$name] = array(
            'name'      => $name,
            'timestamp' => microtime(true),
            'stopped'   => false,

            'timer'     => microtime(true),
            'realmem'   => memory_get_usage(true),
            'emalloc'   => memory_get_usage(),
        );
        return $this;
    }

    /**
     * End a profiler
     *
     * @param string $name
     * @return Profiler
     */
    public function end($name = 'PI')
    {
        if (empty($this->timers[$name])) {
            $this->start($name);
        }
        if (!empty($this->timers[$name]['stopped'])) {
            return $this;
        }
        $this->timers[$name]['stopped'] = true;

        $this->timers[$name]['timer'] = microtime(true) - $this->timers[$name]['timer'];
        $this->timers[$name]['realmem'] = memory_get_usage(true) - $this->timers[$name]['realmem'];
        $this->timers[$name]['emalloc'] = memory_get_usage() - $this->timers[$name]['emalloc'];

        return $this;
    }


    /**
     * Write a profiler
     *
     * @param string $name
     * @return Profiler
     */
    protected function write($name = 'PI')
    {
        $this->end($name);

        foreach ($this->writers->toArray() as $writer) {
            $writer->doProfiler($this->timers[$name]);
        }
        unset($this->timers[$name]);

        return $this;
    }
}
