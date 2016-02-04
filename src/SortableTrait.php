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
use Illuminate\Support\Facades\Input;
use RuntimeException;

trait SortableTrait
{
    /**
     * The parameter name for sorting.
     *
     * @param string
     */
    protected $sortParameterName = 'sort';

    /**
     * The default sorting criteria.
     *
     * @param array
     */
    protected $defaultSortCriteria = [];

    /**
     * Scope sorted results.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array                                 $query
     *
     * @return void
     */
    public function scopeSorted(Builder $builder, $query = [])
    {
        $query = (array)($query ?: Input::get($this->sortParameterName, $this->defaultSortCriteria));

        if (empty($query)) {
            $query = $this->defaultSortCriteria;
        }

        if (is_array($query) && array_key_exists($this->sortParameterName, $query)) {
            $query = (array) $query[$this->sortParameterName];
        }

        $criteria = $this->getCriteria($builder, $query);
        $this->applyCriteria($builder, $criteria);
    }

    /**
     * Get the criteria instances.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array                                 $query
     *
     * @return array
     */
    protected function getCriteria(Builder $builder, array $query)
    {
        $criteria = [];
        foreach ($query as $key => $value) {
            $criterion = new Criterion($key, $value);
            if ($this->isFieldSortable($builder, $criterion->getField())) {
                $criteria[] = $criterion;
            }
        }

        return $criteria;
    }

    /**
     * Determines if the field we're asking to sort by is indeed sortable.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $field
     *
     * @return bool
     */
    protected function isFieldSortable(Builder $builder, $field)
    {
        $sortable = $this->getSortableAttributes($builder);

        return in_array($field, $sortable) || in_array('*', $sortable);
    }

    /**
     * Applies the sort criteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array                                 $criteria
     *
     * @return void
     */
    protected function applyCriteria(Builder $builder, array $criteria)
    {
        foreach ($criteria as $criterion) {
            $criterion->apply($builder);
        }
    }

    /**
     * Returns all of the sortable attributes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    protected function getSortableAttributes(Builder $builder)
    {
        $model = $builder->getModel();

        if (method_exists($model, 'getSortableAttributes')) {
            return $model->getSortableAttributes();
        }

        if (property_exists($model, 'sortable')) {
            return $model->sortable;
        }

        throw new RuntimeException(sprintf('Model %s must either implement getSortableAttributes() or have $sortable property set', get_class($model)));
    }
}
