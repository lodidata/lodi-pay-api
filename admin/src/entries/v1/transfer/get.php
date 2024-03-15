<?php

use Admin\src\resource\TransferRecordFormatter;
use Logic\Admin\BaseController;
use Model\TransferRecordModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run(): TransferRecordFormatter
    {
        $params = $this->request->getParams();
        $merchantId = $this->playLoad['merchant_id'];
        $row = TransferRecordModel::query()
            ->with([
                'payConfig:id,name',
            ])->when($merchantId, function ($row) use ($merchantId) {
                $row->where('merchant_id', $merchantId);
            })
            ->latest('id')
            ->filter($params)
            ->paginate($this->pageSize);
        return TransferRecordFormatter::make($row);
    }
};