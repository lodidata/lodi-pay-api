<?php

namespace Model\filter;


class UserFilter extends BaseFilter
{
    public $relations = [
        'tagRelation' => ['tag_id'],
    ];

    public function user_account($query, $value)
    {
        $query->where('user_account', $value);
    }

    public function id($query, $value)
    {
        return $query->where('id', $value);
    }

    public function merchant_id($query, $value)
    {
        $query->where('merchant_id', $value);
    }

    public function tag_id($query, $value)
    {
        $query->where('tag_id', $value);
    }

    public function status($query, $value)
    {
        $query->where('status', $value);
    }


}