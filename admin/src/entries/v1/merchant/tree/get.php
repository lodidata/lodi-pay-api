<?php

use Logic\Admin\BaseController;
use Model\MerchantModel;

//列表
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run(): array
    {
        $params = $this->request->getParams();
        $row = MerchantModel::query()
            ->when($params['merchant_id'] ?? '', function ($row) use ($params) {
                $row->where('id', $params['merchant_id']);
            })
            ->select(['id', 'name', 'account'])
            ->latest('id')->get();
        return $row->toArray();
    }
};