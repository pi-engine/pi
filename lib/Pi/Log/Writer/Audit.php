<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Log\Writer;

use Pi;
use Pi\Log\Logger;
use Pi\Log\Formatter\Audit as AuditFormatter;
use Zend\Log\Writer\AbstractWriter;
use Zend\Log\Formatter\FormatterInterface;

/**
 * Audit writer
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Audit extends AbstractWriter
{
    /** @var array Options */
    protected $options = array();

    /** @var array Event container */
    protected $events = array();

    /** @var array Extra data meta */
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
     * Get formatter for loggder writer
     *
     * @param Formatter $formatter
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
     * Register a message
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
     *
     * return void
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
     * Get extra information:
     *  Application user information, User-Agent, access page, URI, etc.
     *
     * Columns to record
     *
     * - section: varchar(64), front or admin
     * - module: varchar(64)
     * - controller: varchar(64)
     * - action: varchar(64)
     * - time: int(10), time of the event
     * - user: varchar(64), username
     * - ip: varchar(15), IP of the operator
     * - message: text, custom information
     * - extra: text, extra information
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
            if (!in_array(Pi::service('user')->getUser()->role,
                $this->options['role'])) {
                return $this->extra;
            }
        }
        $data['user'] = Pi::service('user')->getUser()->id ?: 0;
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
                && !in_array($segs[0] . '.' . $segs[1] . '.*',
                             $this->options['ip'])
                && !in_array($segs[0] . '.' . $segs[1] . '.' . $segs[2] . '.*',
                             $this->options['ip'])
                && !in_array($segs[0] . '.' . $segs[1] . '.' . $segs[2]
                                . '.' . $segs[3],
                             $this->options['ip'])
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
                    && !in_array($data['module'] . '-' . $data['controller'],
                                 $this->options['page'])
                    && !in_array($data['module'] . '-' . $data['controller']
                                    . '-' . $data['action'],
                                 $this->options['page'])
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
