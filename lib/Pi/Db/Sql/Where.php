<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Db\Sql;

use Laminas\Db\Sql\Exception;
use Laminas\Db\Sql\Predicate;
use Laminas\Db\Sql\Where as ZendWhere;

/**
 * Clause class
 *
 * Creates where clause
 *
 * @see Laminas\Db\Sql\Where
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Where extends ZendWhere
{
    /**
     * {@inheritdoc}
     * @param  \Closure|string|array|Predicate\PredicateInterface $predicates
     * @param  string $defaultCombination
     *      One of the OP_* constants from Predicate\PredicateSet
     */
    public function __construct(
        $predicates = null,
        $defaultCombination = self::COMBINED_BY_AND
    )
    {
        parent::__construct(null, $defaultCombination);
        if ($predicates) {
            $this->addPredicates($predicates);
        }
    }

    /**
     * Canonize predicate elements
     *
     * @see Laminas\Db\Sql\Select::where()
     *
     * @param  \Closure|string|array|Predicate\PredicateInterface $predicate
     *
     * @throws Exception\InvalidArgumentException
     * @return array
     * @deprecated
     */
    public function canonize($predicate)
    {
        $predicates = [];

        if ($predicate instanceof Predicate\PredicateInterface) {
            $predicates[] = $predicate;
        } elseif ($predicate instanceof \Closure) {
            $predicate($this);
        } else {
            if (is_string($predicate)) {
                // String $predicate should be passed as an expression
                $predicate    = (strpos($predicate,
                        Expression::PLACEHOLDER) !== false)
                    ? new Predicate\Expression($predicate)
                    : new Predicate\Literal($predicate);
                $predicates[] = $predicate;
            } elseif (is_array($predicate)) {

                foreach ($predicate as $pkey => $pvalue) {
                    // loop through predicates

                    if (is_string($pkey) && strpos($pkey, '?') !== false) {
                        // First, process strings that
                        // the abstraction replacement character ?
                        // as an Expression predicate
                        $predicate = new Predicate\Expression($pkey, $pvalue);

                    } elseif (is_string($pkey)) {
                        // Otherwise, if still a string,
                        // do something intelligent with the PHP type provided

                        if ($pvalue === null) {
                            // map PHP null to SQL IS NULL expression
                            $predicate = new Predicate\IsNull($pkey, $pvalue);
                        } elseif (is_array($pvalue)) {
                            // if the value is an array, assume IN() is desired
                            $predicate = new Predicate\In($pkey, $pvalue);
                        } elseif (
                            $pvalue instanceof Predicate\PredicateInterface
                        ) {
                            //
                            throw new Exception\InvalidArgumentException(
                                'Using Predicate must not use string keys'
                            );
                        } else {
                            // otherwise assume that array('foo' => 'bar')
                            // means "foo" = 'bar'
                            $predicate = new Predicate\Operator(
                                $pkey,
                                Predicate\Operator::OP_EQ,
                                $pvalue
                            );
                        }
                    } elseif (
                        $pvalue instanceof Predicate\PredicateInterface
                    ) {
                        // Predicate type is ok
                        $predicate = $pvalue;
                    } else {
                        // must be an array of expressions
                        // (with int-indexed array)
                        $predicate = (strpos($pvalue, Expression::PLACEHOLDER)
                            !== false)
                            ? new Predicate\Expression($pvalue)
                            : new Predicate\Literal($pvalue);
                    }
                    $predicates[] = $predicate;
                }
            }
        }

        return $predicates;
    }

    /**
     * Create predicate object
     *
     * @param  string|array $predicate
     * @param  string $combination
     *
     * @return Predicate\Predicate
     */
    public function create($predicate, $combination = null)
    {
        $combination = $combination ? strtoupper($combination) : null;
        //$predicates = $this->canonize($predicate);
        //$result = new Predicate\Predicate($predicates, $combination);
        $result = new Predicate\Predicate;
        $result->addPredicates($predicate, $combination);

        return $result;
    }

    /**
     * Add predicate to set
     *
     * @param  \Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination
     * @return $this
     */
    public function add($predicate, $combination = null)
    {
        $this->addPredicate($this->create($predicate, $combination));

        return $this;
    }
}
