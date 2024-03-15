<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Logic\Admin\Log;
use Model\TransferRecordModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_THIRD_PAYMENT;
    protected $moduleChild = '第三方代付';
    protected $moduleFunName = '代付列表--代付失败操作';

    public function run($id)
    {
        $params = $this->request->getParams();
        $row = TransferRecordModel::query()->findOrFail($id);
        /** @var TransferRecordModel $row */
        if ($row->status != TransferRecordModel::STATUS_PROCESSING_KEY) {
            return $this->lang->set(160);
        }
        $this->paramHandle($params, $row);
        $result = $row->save();

        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】发起代付失败操作,id为【' . $row->id . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }

    private function paramHandle(array $params, TransferRecordModel $row)
    {
        $row->status = TransferRecordModel::STATUS_FAIL_KEY;
        $row->remark = $params['remark'] ?? '';
    }


};