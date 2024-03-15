<?php

use Logic\Admin\BaseController;
use Model\PayConfigModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run(): array
    {
        $params = $this->request->getParams();
        $row = PayConfigModel::query()
            ->with([
                'merchant:id,name,account'
            ])
            ->when($params['merchant_account'] ?? '', function ($row) use ($params) {
                $row->where('merchant_account', $params['merchant_account']);
            })
            ->where('status', PayConfigModel::STATUS_ENABLED);
        if (!empty($this->playLoad['merchant_id'])) {
            $value = $this->playLoad['merchant_id'];
            $row->whereHas('merchant', function ($query) use ($value) {
                $query->where('id', $value);
            });
        }
        $row = $row->select(['id', 'name', 'type', 'merchant_account'])
            ->orderBy('sort')
            ->latest('id')
            ->get();
        return $row->toArray();
    }
};