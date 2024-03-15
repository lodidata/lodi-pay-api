<?php

use Admin\src\resource\ThirdPaymentFormatter;
use Logic\Admin\BaseController;
use Model\PayConfigModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run(): ThirdPaymentFormatter
    {
        $params = $this->request->getParams();
        $row = PayConfigModel::query()
            ->with([
                'merchant:id,name,account'
            ]);
        if (!empty($this->playLoad['merchant_id'])) {
            $value = $this->playLoad['merchant_id'];
            $row->whereHas('merchant', function ($query) use ($value) {
                $query->where('id', $value);
            });
        }
        $row = $row->orderBy('sort')
            ->orderByDesc('id')
            ->filter($params)
            ->paginate($this->pageSize);
        return ThirdPaymentFormatter::make($row);
    }
};