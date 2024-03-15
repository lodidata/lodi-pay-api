<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Model\filter\Filterable;

/**
 * @property string username
 * @property string user_account
 * @property int status
 * @property int merchant_id
 */
class UserModel extends Model
{
    use SoftDeletes;
    use Filterable;

    protected $table = 'user'; //表名
    protected $primaryKey = 'id'; //主键

    protected $fillable = [
        'user_account', 'username', 'merchant_id', 'status'
    ];

    protected $hidden = ['deleted_at'];

    const STATUS_OFF = 0; // 禁用
    const STATUS_ON = 1; // 启用
    const STATUS_ARR = [
        self::STATUS_OFF => '禁用',
        self::STATUS_ON => '正常',
    ];

    public function getStatusLabelAttribute(): string
    {
        return $this->status ? static::STATUS_ARR[static::STATUS_ON] : static::STATUS_ARR[static::STATUS_OFF];
    }

    public function tags(): HasManyThrough
    {
        return $this->hasManyThrough(TagModel::class, UserTagModel::class, 'user_id', 'id', 'id', 'tag_id');
    }

    public function tagRelation(): HasMany
    {
        return $this->hasMany(UserTagModel::class, 'user_id');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(MerchantModel::class, 'merchant_id');
    }

    /**
     * @return HasOne
     * 最后一比代充单
     */
    public function ordersCollectionLast(): HasOne
    {
        return $this->hasOne(OrdersCollectionModel::class, 'user_id');
    }

    /**
     * @return HasOne
     * 最后一笔代付单
     */
    public function ordersPayLast(): HasOne
    {
        return $this->hasOne(OrdersPayModel::class, 'user_id');
    }


}