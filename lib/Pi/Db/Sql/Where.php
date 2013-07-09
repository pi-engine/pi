<?php
/**
 * Where clause class
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
 * @since           3.0
 * @package         Pi\Db
 * @version         $Id$
 */

namespace Pi\Db\Sql;

use Zend\Db\Sql\Where as ZendWhere;
use Zend\Db\Sql\Predicate;

/*
 * Creates where clause
 *
 * @see Zend\Db\Sql\Where
 */
class Where extends ZendWhere
{
    /**
     * {@inheritdoc}
     * @param  \Closure|string|array|Predicate\PredicateInterface $predicates
     * @param  string $defaultCombination One of the OP_* constants from Predicate\PredicateSet
     */
    public function __construct($predicates = null, $defaultCombination = self::COMBINED_BY_AND)
    {
        if ($predicates) {
            $predicates = $this->canonize($predicates);
        }
        parent::__construct($predicates, $defaultCombination);
    }

    /**
     * Canonize predicate elements
     *
     * @see Zend\Db\Sql\Select::where()
     * @param  \Closure|string|array|Predicate\PredicateInterface $predicate
     * @return array
     */
    public function canonize($predicate)
    {
        $predicates = array();

        if ($predicate instanceof Predicate\PredicateInterface) {
            $predicates[] = $predicate;
        } elseif ($predicate instanceof \Closure) {
            $predicate($this);
        } else {
            if (is_string($predicate)) {
                // String $predicate should be passed as an expression
                $predicate = (strpos($predicate, Expression::PLACEHOLDER) !== false)
                    ? new Predicate\Expression($predicate) : new Predicate\Literal($predicate);
                $predicates[] = $predicate;
            } elseif (is_array($predicate)) {

                foreach ($predicate as $pkey => $pvalue) {
                    // loop through predicates

                    if (is_string($pkey) && strpos($pkey, '?') !== false) {
                        // First, process strings that the abstraction replacement character ?
                        // as an Expression predicate
                        $predicate = new Predicate\Expression($pkey, $pvalue);

                    } elseif (is_string($pkey)) {
                        // Otherwise, if still a string, do something intelligent with the PHP type provided

                        if ($pvalue === null) {
                            // map PHP null to SQL IS NULL expression
                            $predicate = new Predicate\IsNull($pkey, $pvalue);
                        } elseif (is_array($pvalue)) {
                            // if the value is an array, assume IN() is desired
                            $predicate = new Predicate\In($pkey, $pvalue);
                        } elseif ($pvalue instanceof Predicate\PredicateInterface) {
                            //
                            throw new Exception\InvalidArgumentException(
                                'Using Predicate must not use string keys'
                            );
                        } else {
                            // otherwise assume that array('foo' => 'bar') means "foo" = 'bar'
                            $predicate = new Predicate\Operator($pkey, Predicate\Operator::OP_EQ, $pvalue);
                        }
                    } elseif ($pvalue instanceof Predicate\PredicateInterface) {
                        // Predicate type is ok
                        $predicate = $pvalue;
                    } else {
                        // must be an array of expressions (with int-indexed array)
                        $predicate = (strpos($pvalue, Expression::PLACEHOLDER) !== false)
                            ? new Predicate\Expression($pvalue) : new Predicate\Literal($pvalue);
                    }
                    $predicates[] = $predicate;
                }
            }
        }
        
        return $predicates;
    }
}
