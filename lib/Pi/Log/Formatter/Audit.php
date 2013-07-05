<?php
/**
 * Pi Audit Formatter
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

namespace Pi\Log\Formatter;
use Zend\Log\Formatter\FormatterInterface;

class Audit implements FormatterInterface
{
    /**
     * Audit table map
     *
     * @see module/system/sql/mysql.system.sql
     * @var array
     */
    protected $columns = array(
        'timestamp'     => 'time',
        'user'          => 'user',
        'ip'            => 'ip',
        'section'       => 'section',
        'module'        => 'module',
        'controller'    => 'controller',
        'action'        => 'action',
        'method'        => 'method',
        'message'       => 'message',
        'extra'         => 'extra',
    );

    protected $dateTimeFormat = '';

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return array    formatted data to write to the log
     */
    public function format($event)
    {
        $data = array();
        $extra = array();
        foreach ($event as $key => $val) {
            if (isset($this->columns[$key])) {
                $data[$this->columns[$key]] = $val;
            } elseif ($key !== 'priority' && $key !== 'priorityName') {
                $extra[$key] = $val;
            }
        }
        if (!empty($event['extra'])) {
            $data['extra'] = array_merge($event['extra'], $extra);
        }
        if (isset($data['extra'])) {
            $data['extra'] = json_encode($data['extra']);
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormat()
    {
        return $this->dateTimeFormat;
    }

    /**
     * {@inheritDoc}
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->dateTimeFormat = (string) $dateTimeFormat;
        return $this;
    }
}
