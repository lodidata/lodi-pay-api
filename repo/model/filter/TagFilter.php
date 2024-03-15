<?php

namespace Model\filter;
class TagFilter extends BaseFilter
{
    public function id($query, $value)
    {
        return $query->where('id', $value);
    }

    public function status($query, $value)
    {
        $query->where('status', $value);
    }

    public function name($query, $value)
    {
        $query->whereIn('name', $value);
    }


}