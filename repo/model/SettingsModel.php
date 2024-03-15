<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Model\filter\Filterable;

/**
 * @property int id
 * @property string name
 * @property string key
 * @property string default_config
 * @property string info
 * @property int sort
 */
class SettingsModel extends Model
{
    protected $table = 'admin_config'; //表名
    protected $primaryKey = 'id'; //主键
    public $timestamps = false;

    protected $fillable = [
        'name', 'key', 'default_config', 'info',
    ];
    protected $hidden = [
        'parent_id', 'info', 'default_config', 'sort'
    ];

    protected $appends = ['content', 'description'];

    public function getContentAttribute()
    {
        return json_decode($this->attributes['default_config']);
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['info'];
    }

}