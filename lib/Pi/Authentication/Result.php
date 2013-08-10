<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Authentication;

use Pi;
use Zend\Authentication\Result as BaseResult;

/**
 * Authentication result
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Result extends BaseResult
{
    /**
     * Profile data retturned by authentication result
     *
     * @var array
     */
    protected $data = array();

    /**
     * Sets the result code, identity, failure messages and success data
     *
     * @param int|array     $code
     * @param mixed         $identity
     * @param array         $messages
     * @param array         $data
     */
    public function __construct(
        $code,
        $identity = '',
        array $messages = array(),
        array $data = array()
    ) {
        if (is_array($code)) {
            extract($code);
        }
        $this->code     = (int) $code;
        $this->identity = $identity;
        $this->messages = $messages;
        $this->setData($data);
    }

    /**
     * Set result data
     *
     * @param array $data
     * @return self
     */
    public function setData($data)
    {
        $this->data = (array) $data;

        return $this;
    }

    /**
     * Returns the result data
     *
     * @param  string|null  $column
     * @return bool|mixed
     */
    public function getData($column = null)
    {
        if (null === $column) {
            $return = $this->data;
        } else {
            $return = isset($this->data[$column])
                ? $this->data[$column] : null;
        }

        return $return;
    }
}
