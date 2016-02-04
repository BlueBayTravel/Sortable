<?php

/*
 * This file is part of Sortable.
 *
 * (c) Blue Bay Travel <developers@bluebaytravel.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BlueBayTravel\Sortable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Criterion
{
    /**
     * The string to use for ascending orders.
     *
     * @var string
     */
    const ORDER_ASCENDING = 'asc';

    /**
     * The string to use for descending orders.
     *
     * @var string
     */
    const ORDER_DESCENDING = 'desc';

    /**
     * The field to sort.
     *
     * @param string
     */
    protected $field;

    /**
     * The order to sort the field.
     *
     * @param string
     */
    protected $order;

    public function __construct($field, $order)
    {
        if (!in_array($order, [static::ORDER_ASCENDING, static::ORDER_DESCENDING])) {
            throw new InvalidArgumentException(sprintf('Invalid order value [%s]', $order));
        }

        $this->field = $field;
        $this->order = $order;
    }

    /**
     * Return the field.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Return the order.
     *
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Apply the criteria to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    public function apply(Builder $builder)
    {
        $sortMethod = 'sort'.Str::studlyCase($this->getField());

        if (method_exists($builder->getModel(), $sortMethod)) {
            call_user_func_array([
                $builder->getModel(),
                $sortMethod,
            ], [
                $builder,
                $this->getOrder(),
            ]);
        } else {
            $builder->orderBy($this->getField(), $this->getOrder());
        }
    }
}
