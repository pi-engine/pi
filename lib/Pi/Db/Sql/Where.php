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
     * Constructor
     *
     * @param  null|string|array $predicates
     * @param  string $defaultCombination
     * @return void
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
     * @param string|array $predicates
     * @return array
     */
    public function canonize($predicates)
    {
        $result = array();
        if (is_string($predicates)) {
            // String $predicate should be passed as an expression
            $result[] = new Predicate\Expression($predicates);
        } elseif (is_array($predicates)) {
            foreach ($predicates as $pkey => $pvalue) {
                // loop through predicates

                if (is_string($pkey) && strpos($pkey, '?') !== false) {
                    // First, process strings that the abstraction replacement character ?
                    // as an Expression predicate
                    $predicate = new Predicate\Expression($pkey, $pvalue);

                } elseif (is_string($pkey)) {
                    // Otherwise, if still a string, do something intelligent with the PHP type provided

                    if (is_null($pvalue)) {
                        // map PHP null to SQL IS NULL expression
                        $predicate = new Predicate\IsNull($pkey, $pvalue);
                    } elseif (is_array($pvalue)) {
                        // if the value is an array, assume IN() is desired
                        $predicate = new Predicate\In($pkey, $pvalue);
                    } else {
                        // otherwise assume that array('foo' => 'bar') means "foo" = 'bar'
                        $predicate = new Predicate\Operator($pkey, Predicate\Operator::OP_EQ, $pvalue);
                    }
                } elseif ($pvalue instanceof Predicate\PredicateInterface) {
                    // Predicate type is ok
                    $predicate = $pvalue;
                } else {
                    // must be an array of expressions (with int-indexed array)
                    $predicate = new Predicate\Expression($pvalue);
                }
                $result[] = $predicate;
            }
        } elseif ($predicates) {
            $result[] = $predicates;
        }
        return $result;
    }
}
