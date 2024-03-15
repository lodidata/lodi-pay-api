<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;
use Model\PayConfigModel;

class TransferRecordValidate extends BaseValidate
{
    // 验证规则
    protected $rule = [
        'merchant_id' => 'require|number',
        'pay_config_id' => 'require|exists:\\Model\\PayConfigModel,id',
        'bank_card_name' => 'require|max:100',
        'bank_card_account' => 'require|max:100',
        'bank' => 'require|in:gcash,bpi,Unibank,mbt,LBOB,SBC,UBP,PNB,CBC,EWBC,RCBC,UCPB,PSB,AUB,PBC,DBP,AB,Asenso,BM,BC,BK,Bayad,BNB,CB,CARD Bank
,CLB,CBS,Coins,CTBC,DCDB,DB,ESB,GP,IB,ISLA,JC,Komo,LSB,MBS,MBP,MCCB,NB,OP,PRB,PMP,PBB,PTC,PDB,QB,QCRB,RSB,RBB,SB,SP,SCB,STP,SLB,SSB,TC,USB,USSC,VB,WDB',
        'pay_inner_order_sn' => 'require|max:100',
    ];

    protected $field = [
        'name' => '站点名称'
    ];

    protected $message = [
        'merchant_id.require' => '请填写商户id',
        'pay_config_id.require' => '请选择代付方式',
        'bank_card_name.require' => '请输入收款人',
        'bank_card_account.require' => '请输入收款账号',
        'pay_inner_order_sn.require' => '请输入提现订单号',
    ];

    protected $scene = [
        'post' => [
            'merchant_id',
            'pay_config_id',
            'bank_card_name',
            'bank_card_account',
            'pay_inner_order_sn',
        ],
    ];

}