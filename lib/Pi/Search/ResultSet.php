<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Search;

use Countable;
use Iterator;

/**
 * Search ResultSet class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ResultSet implements Iterator, Countable
{
    /** @var  int */
    protected $total = 0;

    /** @var  array */
    protected $array = array();

    /**
     * @var int
     */
    protected $position = 0;

    public function __construct($total = 0, array $data = array())
    {
        $this->setTotal($total);
        $this->setData($data);
    }

    public function setTotal($total)
    {
        $this->total = (int) $total;

        return $this;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setData(array $data)
    {
        foreach ($data as $item) {
            $this->array[] = new Result($item);
        }

        return $this;
    }

    public function getData()
    {
        return $this->array;
    }

    public function count()
    {
        return count($this->array);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->array[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->array[$this->position]);
    }
}
