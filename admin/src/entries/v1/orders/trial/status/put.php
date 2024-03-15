<?php

use Lib\Validate\Admin\TrialValidate;
use Logic\Admin\BaseController;
use Logic\Admin\Log;
use Model\AdminLogModel;
use Model\OrdersAttachmentModel;
use Model\OrdersCollectionModel;
use Model\OrdersCollectionTrialModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_ORDER;
    protected $moduleChild = '争议订单';
    protected $moduleFunName = '争议订单上传凭证';

    public function run($id)
    {
        (new TrialValidate)->paramsCheck('status.put', $this->request, $this->response);
        $params = $this->request->getParams();
        $row = OrdersCollectionTrialModel::query()->findOrFail($id);
        $status = $this->getCollectionStatus($params['type']);

        $result = true;
        DB::pdo()->beginTransaction();
        try {
            //更新collection订单状态值
            /** @var OrdersCollectionTrialModel $row */
            $collectResult = $this->updateCollectionStatus($row->orders_collection_sn, $status);

            //附件添加
            $attachmentResult = $this->createAttachment($row->orders_collection_sn, $params);

            //更新争议文本说明
            $trialResult = $this->updateTrial($row, $params);

            if ($collectResult && $attachmentResult && $trialResult) {
                DB::pdo()->commit();
            } else {
                throw new Exception($this->lang->text(-2));
            }
        } catch (Exception $e) {
            DB::pdo()->rollBack();
            $result = false;
        }

        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】给充值订单号【' . $row->orders_collection_sn . '】争议订单上传凭证',
        ];
        $this->writeAdminLog($logArr);
        return $this->lang->set($result ? 0 : -2);
    }

    private function updateCollectionStatus(string $inner_order_sn, int $status): int
    {
        return OrdersCollectionModel::query()->where('inner_order_sn', $inner_order_sn)->update(['status' => $status]);
    }

    private function createAttachment(string $inner_order_sn, array $params)
    {
        return OrdersAttachmentModel::query()->create([
            'inner_order_sn' => $inner_order_sn,
            'url' => $params['url'],
            'type' => OrdersAttachmentModel::TYPE_COLLECTION,
        ]);
    }

    private function updateTrial(OrdersCollectionTrialModel $row, array $params): bool
    {
        $row->remark = $params['remark'];
        return $row->save();
    }

    /**
     * 获取状态值
     * @param $type
     * @return mixed|string
     */
    private function getCollectionStatus($type)
    {
        $status = '';
        /** @var OrdersCollectionTrialModel $row */
        if ($type == TrialValidate::UPLOAD_NAME) {
            //代上传完成之后就进入待确认状态
            $status = OrdersCollectionModel::$statusArr['pre_check']['value'];
        } elseif ($type == TrialValidate::CONFIRM_NAME) {
            //代确认完成之后就进入订单完结状态
            $status = OrdersCollectionModel::$statusArr['complete']['value'];
        }
        return $status;
    }

};