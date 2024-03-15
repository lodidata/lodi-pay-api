<?php

use Logic\Admin\BaseController;
use Model\CurrencyModel;

//获取币种
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken'
    ];

    public function run()
    {
        return CurrencyModel::query()->where('status', '=', CurrencyModel::STATUS_ON)->get()->toArray();
    }
};