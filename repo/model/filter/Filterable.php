<?php

declare(strict_types=1);

namespace Model\filter;

trait Filterable
{

    /**
     * Creates local scope to run the filter.
     *
     * @param $query
     * @param array $input
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $input = [])
    {

        $table = $this->getTable();
        $filter =  str_replace('_', '', ucwords($table, '_'));
        $filterPath = "\\Model\\filter\\".$filter."Filter";
        // Create the model filter instance
        $modelFilter = new $filterPath($query, $input);

        // Return the filter query
        return $modelFilter->handle();
    }
}
