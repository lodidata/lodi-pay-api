<?php

use Lib\Validate\Admin\OrdersValidate;
use Logic\Admin\BaseController;
use Logic\Admin\Log;
use Logic\PayAdmin\Api;
use Model\AdminLogModel;
use Model\OrdersAttachmentModel;
use Model\OrdersCollectionModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_ORDER;
    protected $moduleChild = '充值订单';
    protected $moduleFunName = '充值订单上传凭证';

    public function run($id)
    {
        (new OrdersValidate)->paramsCheck('status.put', $this->request, $this->response);
        $params = $this->request->getParams();

        /** @var OrdersCollectionModel $row */
        $row = OrdersCollectionModel::query()->findOrFail($id);
        $statusArr = [
            OrdersCollectionModel::$statusArr['pre_upload_ticket']['value'],
            OrdersCollectionModel::$statusArr['upload_ticket_timeout']['value'],
        ];
        if (!in_array($row->status, $statusArr) ||
            $row->order_type != OrdersCollectionModel::$orderTypes['local_pay']['value']) {
            return $this->lang->set(203);
        }

        //进入待确认状态
        $row->status = OrdersCollectionModel::$statusArr['pre_check']['value'];
        $remark = '客服代上传凭证:' . $params['remark'];
        $row->remark = !empty($row->remark) ? $row->remark . '|' . $remark : $remark;

        //更新collection订单状态值
        $row->save();

        //附件添加
        $this->createAttachment($row->inner_order_sn, $params);

        $logArr = [
            'status' => AdminLogModel::STATUS_ON,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】给充值订单号【' . $row->inner_order_sn . '】充值订单上传凭证',
        ];
        $this->writeAdminLog($logArr);

        $data['inner_order_sn'] = $row->inner_order_sn;
        $this->uploadCert($data);

        return $this->lang->set(0);
    }

    private function createAttachment(string $inner_order_sn, array $params)
    {
        OrdersAttachmentModel::query()->create([
            'inner_order_sn' => $inner_order_sn,
            'url' => $params['url'],
            'type' => OrdersAttachmentModel::TYPE_COLLECTION,
        ]);
    }

    private function uploadCert(array $data)
    {
        $result = (new Api($this->ci))->uploadCert($data);
        $logArr = [
            'status' => $result['code'] == 0 ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'remark' => '【' . $this->playLoad['admin_name'] . '】同步订单上传凭证，请求结果信息为：' . $result['message'],
            'record' => $data,
        ];
        $this->writeAdminLog($logArr);
    }

};