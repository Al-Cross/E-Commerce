<?php

namespace App\Filters;

use Illuminate\Http\Request;

abstract class Filters
{
    protected $filters;
    protected $builder;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply the filters to the builder.
     *
     * @param  Eloquent query $builder
     * @return Eloquent query
     */
    public function apply($builder)
    {
        $this->builder = $builder;

        foreach ($this->getFilters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                $this->$filter($value);
            }
        }
    }

    /**
     * Fetch all selected filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->intersect($this->filters);
    }

    /**
     * Get the filters from the request. If there aren't any, return empty array.
     *
     * @param  array  $keys  The filters
     * @return array
     */
    public function intersect($keys)
    {
        return array_filter($this->request->only(is_array($keys) ? $keys : func_get_args()));
    }
}
