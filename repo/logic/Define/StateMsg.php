<?php

namespace Logic\Define;

/**
 * 返回状态码说明类
 */
class StateMsg
{
    public static $msgArr =
        [
            State::SUCCESS                                             => '成功',
            State::PARAMETER_ERROR                                     => '参数错误',
            State::UNAUTHORIZED_ACCESS                                 => '非法访问',
            State::SIGNATURE_ERROR                                     => '签名错误',
            State::SYSTEM_ERROR                                        => '系统错误',
            State::FREQUENT_OPERATION                                  => '操作频繁，请稍后再试',
            State::AGENT_DISABLED                                      => '代理被禁用',
            State::AGENT_NOT_EXIST                                     => '代理帐号不存在',
            State::GAME_MENU_DISABLED                                  => '游戏厂商被禁用',
            State::GAME_PLATFORM_NOT_EXIST                             => '游戏厂商不存在',
            State::GAME_3th_DISABLED                                   => '第三方子游戏被禁用',
            State::GAME_3th_NOT_EXIST                                  => '第三方子游戏不存在',
            State::GAME_DOES_NOT_MATCH_THE_GAME_TYPE                   => '游戏与游戏类型不匹配',
            State::AGENT_NOT_ALLOW_LOGIN                               => '代理不允许注册登录',
            State::AGENT_NOT_ALLOW_TRANSFER                            => '代理不允许转账',
            State::AGENT_NOT_ALLOW_ORDER                               => '代理不允许拉单',
            state::ZERO_BALANCE                                        => '充提点数为0',
            state::ABNORMAL_BALANCE                                    => '点数异常',
            state::INVALID_TRANSACTION_CODE                            => '充提方式代码错误',
            state::WITHDRAWAL_AMOUNT_IS_GREATER_THAN_AVAILABLE_BALANCE => '提款点数大于玩家持有点数',
            state::TRANSACTION_ID_IS_TOO_LONG                          => '交易对应单号过长',
            state::DUPLICATE_TRANSACTION_ID                            => '交易对应单号重复',
            state::INVALID_START_TIME                                  => '起始时间错误',
            state::INVALID_END_TIME                                    => '结束时间错误',
            state::START_TIME_EXCEEDS_END_TIME                         => '起始时间超前结束时间',
            state::PAGE_NUMBER_IS_NOT_A_NUMBER                         => '页码不是数字',
            state::TRANSACTION_ID_IS_EMPTY                             => '交易对应单号为空',
            state::INVALID_BET_RECORD_PARAMETERS                       => '详细投注记录参数错误',
            state::TRANSACTION_ID_IS_NOT_A_NUMBER                      => '交易单号不是数字',
            state::INVALID_CORRESPONDING_ID                            => '对应单号不合法',
            state::JACKPOT_MODE_IS_NOT_AVAILABLE_FOR_THIS_GAME         => '此游戏编号未提供彩金模式',
            state::THIS_LANGUAGE_VERSION_IS_NOT_AVAILABLE              => '未开放此语系',
            state::THIS_LANGUAGE_VERSION_HAS_BEEN_SET                  => '已设定此语系',
            state::DATA_IS_NOT_IN_JSON_FORMAT                          => '传入资料不是JSON',
            state::NUMBER_OF_DATA_RECORDS_IS_NOT_A_NUMBER              => '资料笔数不是数字',
            state::AMOUNT_EXCEEDS_THE_UPPER_LIMIT                      => '点数超过上限额度',
            state::SEARCH_TIME_EXCEEDS_THE_MAXIMUM_RANGE               => '搜寻时间超出最大范围',
            state::TRANSACTION_ID_DOES_NOT_EXIST                       => '交易单号不存在',
            state::INVALID_GAME_TYPE                                   => '游戏类型错误',
            state::PAGE_NUMBER_EXCEEDS_TOTAL_PAGES                     => '页码超过总页数',
            state::INVALID_PAGE_NUMBER                                 => '页码错误',
            state::INVALID_RECORD_COUNT                                => '笔数错误',
            state::INVALID_SORTING                                     => '排序错误',
            state::SEARCH_TIME_EXCEEDS_THE_MINIMUM_RANGE               => '查询时间超过最小范围',
            state::FAILED_TO_GET_MERCHANT_KEY                          => '无法取得商户密钥',
            state::FAILED_TO_DECRYPT_DATA                              => '资料解密失败',
            state::FAILED_TO_VERIFY_MERCHANT_SIGNATURE                 => '商户签章比对失败',
            state::MERCHANT_CODE_IS_DISABLED                           => '此商户代码禁用',
            state::METHOD_IS_DISABLED                                  => '此方法禁用',
            state::METHOD_IS_DISABLED_FOR_THIS_MERCHANT_CODE           => '此商户代码的方法禁用',
            state::GAME_DOES_NOT_EXIST                                 => '游戏不存在',
            state::GAME_IS_CLOSED                                      => '游戏关闭',
            state::ACCOUNT_IS_LOCKED                                   => '账号锁定中',
            state::GAME_IS_UNDER_MAINTENANCE                           => '游戏维护中',
            state::NO_ACCESS_FOR_THIS_ACCOUNT                          => '没有此账号的权限',
            state::DISALLOWED_IP                                       => '不允许的IP',
            state::METHOD_IS_UNDER_MAINTENANCE                         => '此方法维护中',
            state::ACCOUNT_DOES_NOT_EXIST                              => '账号不存在',
            state::ACCOUNT_IS_TOO_LONG                                 => '账号过长',
            state::DUPLICATE_ACCOUNT                                   => '账号重复',
            state::ACCOUNT_IS_ONLINE                                   => '账号在线',
            state::ACCOUNT_IS_OFFLINE                                  => '账号不在线',
            state::ACCOUNT_IS_TOO_SHORT                                => '账号过短',
            state::URL_IS_BLANK                                        => 'URL是空白',
            state::MERCHANT_RESPONSE_IS_EMPTY                          => '商户响应为空',
            state::RETURNED_DATA_IS_NOT_IN_JSON_FORMAT                 => '回传资料不是JSON格式',
            state::VERIFICATION_FAILED                                 => '验证失败',
            state::NO_VERIFICATION_RESULT_IS_RETURNED                  => '没有回传验证结果',
            state::PLATFORM_BALANCE_RETRIEVAL_FAILURE                  => '取得平台点数非数字',
            state::NO_BALANCE_IS_RETURNED                              => '没有回传点数',
            state::FAILED_TO_RETRIEVE_BALANCE                          => '点数取得失败',
            state::FAILED_TO_WRITE_TRANSACTION_RECORD                  => '交易单写入失败',
            state::FAILED_TO_PERFORM_TOP_UP_OR_DOWN                    => '充提错误',
            state::BLANK_PLAYER_INFORMATION_QUERY                      => '玩家信息查询空白',
            state::BLANK_GAME_RECORD_COUNT_QUERY                       => '游戏记录总数查询空白',
            state::BLANK_GAME_RECORD_QUERY                             => '游戏记录查询空白',
            state::BLANK_TRANSACTION_RECORD_COUNT_QUERY                => '交易记录总数查询空白',
            state::NO_DATA_IS_FOUND                                    => '查无资料',
            state::URL_EXPIRED                                         => '网址过期',
            state::GAME_CARRIER_URL_NOT_FOUND                          => '找不到游戏载体网址',
            state::LANGUAGE_VERSION_MODIFICATION_FAILED                => '语系修改失败',
            state::INVALID_DATA_TRANSMISSION_METHOD                    => '资料传输方式错误',
            state::FAILED_TO_RETRIEVE_DATA                             => '无法抓取资料',
            state::VISITOR_IP_IS_TOO_LONG                              => '访问者IP过长',
            state::UNEXPECTED_ERROR                                    => '无法预期的错误',
            state::MERCHANT_CODE_IS_MISSING                            => '没有带入商户代码',
            state::CURRENCY_IS_MISSING                                 => '没有带入币别',
            state::DATA_IS_MISSING                                     => '没有带入资料',
            state::USER_SIGN_IS_MISSING                                => '没有带入商户签章',
            state::BEGIN_TIME_IS_MISSING                               => '没有带入开始时间',
            state::ENDING_TIME_IS_MISSING                              => '没有带入结束时间',
            state::PAGE_IS_MISSING                                     => '没有带入页数',
            state::DATE_TIME_IS_MISSING                                => '没有带入日期',
            state::GAME_TYPE_IS_MISSING                                => '没有带入游戏类型',
            state::GAME_RECORD_TIME_IS_MISSING                         => '没有带入游戏记录编号',
            state::PARAMS_IS_MISSING                                   => '参数带入错误，请确认参数是否正确',
            state::AGENT_GAME_PLATFORM_NOT_EXIST                       => '代理游戏厂商不存在',
            State::AGENT_GAME_MENU_DISABLED                            => '代理游戏厂商被禁用',
            State::AGENT_CURRENCY_NOT_EXIST                            => '代理贷币不存在',
        ];

    public static function getMsg($key)
    {
        return self::$msgArr[$key] ?? null;
    }

}