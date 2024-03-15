<?php

namespace  Model;

use Illuminate\Database\Eloquent\Model;

class UserTagModel extends Model{

    protected $table = 'user_tag'; //表名
    protected $primaryKey = 'id'; //主键

    public $timestamps =false;

    protected $fillable = [
       'user_id', 'tag_id'
    ];
}