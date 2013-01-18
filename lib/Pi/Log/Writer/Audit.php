<?php
/**
 * Pi Audit Writer
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

namespace Pi\Log\Writer;

use Pi;
use Pi\Log\Logger;
use Pi\Log\Formatter\Audit as AuditFormatter;
use Zend\Log\Writer\AbstractWriter;
use Zend\Log\Formatter\FormatterInterface;

class Audit extends AbstractWriter
{
    protected $options = array();
    protected $events = array();
    protected $extra;

    /**
     * Constructor
     *
     * @param  array $params Array of options
     */
    public function __construct(array $params = array())
    {
        $this->options = $params;
    }

    /**
     * get formatter for loggder writer
     *
     * @param  Formatter $formatter
     * @return self
     */
    public function formatter()
    {
        if (!$this->formatter) {
            $this->formatter = new AuditFormatter;
        }
        return $this->formatter;
    }

    /**
     * Perform shutdown activites such as closing open resources
     *
     * @return void
     */
    public function shutdown()
    {
        $this->commit();
    }

    /**
     * Write a message to syslog.
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event)
    {
        if ($event['priority'] != Logger::AUDIT) {
            return;
        }

        $this->events[] = $event;
    }

    /**
     * Store logged events into storage
     */
    protected function commit()
    {
        $extra = $this->getExtra();
        if ($extra === false) {
            return;
        }

        foreach ($this->events as $event) {
            $event = array_merge($event, $extra);
            $data = $this->formatter()->format($event);
            $row = Pi::model('audit')->createRow($data);
            $row->save();
            unset($row);
        }
    }

    /**
     * Get extra information: Application user information, User-Agent, access page, URI, etc.
     *
     * <url> Columns to record
     *      <li>`section`: varchar(64), front or admin</li>
     *      <li>`module`: varchar(64)</li>
     *      <li>`controller`: varchar(64)</li>
     *      <li>`action`: varchar(64)</li>
     *      <li>`time`: int(10), time of the event</li>
     *      <li>`user`: varchar(64), username</li>
     *      <li>`ip`: varchar(15), IP of the operator</li>
     *      <li>`message`: text, custom information</li>
     *      <li>`extra`: text, extra information</li>
     * </ul>
     *
     * @return array
     */
    protected function getExtra()
    {
        if (null !== $this->extra) {
            return $this->extra;
        }
        $this->extra = false;
        $data = array();
        if (!empty($this->options['role'])) {
            if (!in_array(Pi::registry('user')->role, $this->options['role'])) {
                return $this->extra;
            }
        }
        $data['user'] = Pi::registry('user') ? Pi::registry('user')->id : 0;
        if (!empty($this->options['user'])) {
            if (!in_array($data['user'], $this->options['user'])) {
                return $this->extra;
            }
        }

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $data['ip'] = $ip;
        if (!empty($this->options['ip'])) {
            $segs = explode('.', $data['ip']);
            if (!in_array($segs[0] . '.*', $this->options['ip'])
                && !in_array($segs[0] . '.' . $segs[1] . '.*', $this->options['ip'])
                && !in_array($segs[0] . '.' . $segs[1] . '.' . $segs[2] . '.*', $this->options['ip'])
                && !in_array($segs[0] . '.' . $segs[1] . '.' . $segs[2] . '.' . $segs[3], $this->options['ip'])
            ) {
                return $this->extra;
            }
        }

        $request = Pi::engine()->application()->getRequest();
        /*
        $data['method'] = $request->getMethod();
        if (!empty($this->options['method'])) {
            if (!in_array($data['method'], $this->options['method'])) {
                return $this->extra;
            }
        }
        */

        $event = Pi::engine()->application()->getMvcEvent();
        if (!$event) {
            return $this->extra;
        }
        $routeMatch = $event->getRouteMatch();
        if ($routeMatch) {
            $data['module'] = $routeMatch->getParam('module');
            $data['controller'] = $routeMatch->getParam('controller');
            $data['action'] = $routeMatch->getParam('action');
            if (!empty($this->options['page'])) {
                if (!in_array($data['module'], $this->options['page'])
                    && !in_array($data['module'] . '-' . $data['controller'], $this->options['page'])
                    && !in_array($data['module'] . '-' . $data['controller'] . '-' . $data['action'], $this->options['page'])
                ) {
                    return $this->extra;
                }
            }
        }

        $data['extra'] = array(
            'uri'           => $request->getRequestUri(),
            'user-agent'    => $_SERVER['HTTP_USER_AGENT'],
        );
        if ($request->isPost()) {
            $data['content'] = $request->getContent();
        }
        $data['section'] = Pi::engine()->section();

        $this->extra = $data;
        return $this->extra;
    }
}
