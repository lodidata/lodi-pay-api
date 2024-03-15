<?php

use Illuminate\Support\Arr;
use Model\OrdersPayModel;
use Model\OrdersAttachmentModel;
use Model\OrdersCollectionModel;
use Logic\Admin\BaseController;
use Lib\Validate\Admin\FinancialStatementsValidate;

return new class() extends BaseController {
    //前置方法
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run($id)
    {
        $params = $this->request->getParams();
        (new FinancialStatementsValidate())->paramsCheck( 'show', $this->request, $this->response ); // 批量更新请求参数
        $where = [];
        if (!empty($params['created_at'][0]) && !empty($params['created_at'][1])) {
            $where = [$params['created_at'][0], $params['created_at'][1]];
        }
        $table_id = $params['type'] ? 'p.merchant_id' : 'c.merchant_id';
        $status = $params['type'] ? ['c.status' => OrdersPayModel::$status['complete']['value']] : ['c.status' => OrdersCollectionModel::$statusArr['complete']['value']];
        $res = DB::table('orders_collection as c')
            ->join('orders_pay as p', 'p.inner_order_sn', '=', 'c.pay_inner_order_sn')
            ->where([$table_id => $id])->where($status);
        if(!empty($where)){
            $res->whereBetween('c.created_at', $where);
        }
        $res = $res->select([
            'c.pay_inner_order_sn', 'c.merchant_account as c_merchant_account','p.merchant_account as p_merchant_account', 'p.user_account as p_user_account', 'c.user_account as c_user_account',
            'c.created_at', 'c.merchant_id as c_merchant_id', 'p.merchant_id as p_merchant_id', 'p.payment as p_payment', 'p.amount as p_amount', 'p.currency as p_currency',
            'c.currency as c_currency', 'c.inner_order_sn', 'c.payment as c_payment', 'c.amount as c_amount', 'c.remark as c_remark', 'p.remark as p_remark', 'c.status as c_status', 'p.status as p_status',
            'p.order_sn as p_order_sn','c.order_sn as c_order_sn'
        ])->paginate($this->pageSize)->toArray();
        if(!empty($res['data'])){
            $res['data'] = $this->getAttachment($res['data'], $params['type']);
        }
        $common = ['page' => $params['page'], 'page_size' => $params['page_size'], 'total' => $res['total']];
        return $this->lang->set(0, [], $res['data'], $common);
    }

    /**
     * @param $type
     * @param $res
     * @return mixed
     */
    public function getAttachment($res, $type){
        $colloction_sn = $pay_sn = [];
        $merchant_names = $this->getMerchant($res);
        foreach ($res as &$v){
            $colloction_sn[] = $v->inner_order_sn;
            $pay_sn[] = $v->pay_inner_order_sn;
            $v->pay_url = [];
            $v->p_merchant_id_name = $merchant_names[$v->p_merchant_id] ?? '';
            $v->c_merchant_id_name = $merchant_names[$v->c_merchant_id] ?? '';
            $v->collection_url = [];
            $v->c_status_label = Arr::get(Arr::pluck(OrdersCollectionModel::$statusArr, 'message', 'value'), $v->c_status) ?? '';
            $v->p_status_label = Arr::get(Arr::pluck(OrdersPayModel::$status, 'message', 'value'), $v->p_status) ?? '';
            $v->status = $v->c_status_label;//$type ? $v->p_status_label : $v->c_status_label;
            $v->order_sn = $v->p_order_sn; //$type ? $v->p_order_sn : $v->c_order_sn;
        }
        $order = OrdersAttachmentModel::query()->where('type', '=', OrdersAttachmentModel::TYPE_COLLECTION)
            ->whereIn('inner_order_sn',$colloction_sn)
            ->pluck('url', 'inner_order_sn')->toArray();
        $pay_order = OrdersAttachmentModel::query()->where('type', '=', OrdersAttachmentModel::TYPE_PAY)
            ->whereIn('inner_order_sn',$pay_sn)
            ->pluck('url', 'inner_order_sn')->toArray();

        if(!empty($order)){
            $obj = new OrdersAttachmentModel();
            foreach ($order as $k => &$v){
                $obj->getUrlAttribute(json_encode($v));
            }
            foreach ($res as &$v){
                $v->collection_url = $order[$v->inner_order_sn] ?? [];
            }
        }
        if(!empty($pay_order)){
            $obj = new OrdersAttachmentModel();
            foreach ($pay_order as $k => &$v){
                $obj->getUrlAttribute(json_encode($v));
            }
            foreach ($res as &$v){
                $v->pay_url = $pay_order[$v->pay_inner_order_sn] ?? [];
            }
        }
        return $res;
    }

    /**
     * @param $res
     * @return array
     */
    protected function getMerchant($res): array
    {
        $merchant_ids = [];
        foreach ($res as $v){
            if(!in_array($v->c_merchant_id, $merchant_ids)){
                $merchant_ids[] = $v->c_merchant_id;
            }
            if(!in_array($v->p_merchant_id, $merchant_ids)){
                $merchant_ids[] = $v->p_merchant_id;
            }
        }
        return \Model\MerchantModel::query()->whereIn('id', $merchant_ids)->pluck('name', 'id')->toArray();
    }


};