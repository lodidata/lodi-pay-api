<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;

class TrialValidate extends BaseValidate
{
    const UPLOAD_NAME = 'upload';
    const CONFIRM_NAME = 'confirm';

    // 验证规则
    protected $rule = [
        'action_type' => 'require|in:1,2,3',
        'problem_source' => 'require|in:0,1,2,3,4',
        'description' => 'require|max:255',
        'remark' => 'require|max:255',
        'url' => 'require',
        'type' => 'require|in:' . self::UPLOAD_NAME . ',' . self::CONFIRM_NAME,
        'bank_card_name' => 'require',
        'bank_card_account' => 'require',
        'bank' => 'require|in:gcash,bpi,Unibank,mbt,LBOB,SBC,UBP,PNB,CBC,EWBC,RCBC,UCPB,PSB,AUB,PBC,DBP,AB,Asenso,BM,BC,BK,Bayad,BNB,CB,CARD Bank
,CLB,CBS,Coins,CTBC,DCDB,DB,ESB,GP,IB,ISLA,JC,Komo,LSB,MBS,MBP,MCCB,NB,OP,PRB,PMP,PBB,PTC,PDB,QB,QCRB,RSB,RBB,SB,SP,SCB,STP,SLB,SSB,TC,USB,USSC,VB,WDB',
        'pay_type' => 'require|exists:\\Model\\PayConfigModel,type',
    ];

    protected $field = [
        'remark' => '备注',
    ];

    protected $message = [
        'action_type.require' => '请选择处理方案',
        'action_type.in' => '请选择处理方案',
        'problem_source.require' => '请选择问题归责',
        'problem_source.in' => '请选择问题归责',
        'description.require' => '请输入描述',
        'description.max' => '描述文本最大为255位',
        'remark.max' => '备注最大为255位',
        'remark.require' => '备注不能为空',
        'url.require' => '请上传图片',
        'type.require' => 'type参数缺失',
        'type.in' => 'type参数错误',
        'bank_card_name.require' => '请输入收款人姓名',
        'bank_card_account.require' => '请输入收款人账号',
        'bank.require' => '请选择银行',
        'bank.in' => '银行代码参数错误',
        'pay_type.require' => '请选择代付',
        'pay_type.exists' => '代付参数错误',
    ];

    protected $scene = [
        'put' => [
            'action_type',
            'problem_source',
            'description',
        ],
        'status.put' => [
            'url',
            'remark',
            'type',
        ],
        'pay.post' => [
            'remark',
            'pay_type',
        ],
    ];

}