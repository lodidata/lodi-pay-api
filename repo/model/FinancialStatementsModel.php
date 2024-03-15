<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Model\filter\Filterable;

/**
 * @property int merchant_id
 */
class FinancialStatementsModel extends Model
{
    use Filterable;

    protected $table = 'financial_statements'; //表名
    protected $primaryKey = 'id'; //主键

    protected $fillable = [
        'merchant_id', 'merchant_name', 'finance_date'
    ];

    public function merchant(): HasOne
    {
        return $this->hasOne(MerchantModel::class, 'id', 'merchant_id');
    }

}