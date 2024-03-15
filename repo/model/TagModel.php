<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Model\filter\Filterable;

/**
 * @property string name
 * @property int status
 */
class TagModel extends Model
{
    use Filterable;
    use SoftDeletes;

    protected $table = 'tag'; //表名
    protected $primaryKey = 'id'; //主键

    protected $fillable = [
        'name', 'description', 'admin_id', 'status'
    ];

    const STATUS_OFF = 0; // 禁用
    const STATUS_ON = 1; // 启用
    const STATUS_ARR = [
        self::STATUS_OFF => '禁用',
        self::STATUS_ON => '正常',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminModel::class, 'admin_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status ? static::STATUS_ARR[static::STATUS_ON] : static::STATUS_ARR[static::STATUS_OFF];
    }

    public function userTag(): HasMany
    {
        return $this->hasMany(UserTagModel::class, 'tag_id');
    }

}