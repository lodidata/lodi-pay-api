<?php

namespace Logic\Define;


/**
 * 返回状态码类
 */
class State
{
    /***
     * start==========================================公共参数=================
     */
    //成功
    const SUCCESS = 0;
    //参数错误
    const PARAMETER_ERROR = 100;
    //非法访问
    const UNAUTHORIZED_ACCESS = 101;
    //签名错误
    const SIGNATURE_ERROR = 102;
    //系统错误
    const SYSTEM_ERROR = 103;
    //操作频繁，请稍后再试
    const FREQUENT_OPERATION = 104;

    /***
     * end==================================================================================
     */

    /***
     * start==========================================代理&游戏=================
     */
    //代理被禁用
    const AGENT_DISABLED = 201;
    //代理帐号不存在
    const AGENT_NOT_EXIST = 202;
    //代理游戏厂商不存在
    const AGENT_GAME_PLATFORM_NOT_EXIST = 204;
    //代理游戏厂商被禁用
    const AGENT_GAME_MENU_DISABLED = 205;
    //代理贷币不存在
    const AGENT_CURRENCY_NOT_EXIST = 206;

    //游戏厂商被禁用
    const GAME_MENU_DISABLED = 210;
    //游戏厂商不存在
    const GAME_PLATFORM_NOT_EXIST = 211;

    //第三方子游戏被禁用
    const GAME_3th_DISABLED = 220;
    //第三方子游戏不存在
    const GAME_3th_NOT_EXIST = 221;
    //游戏与游戏类型不匹配
    const GAME_DOES_NOT_MATCH_THE_GAME_TYPE = 222;

    //代理不允许注册登录
    const AGENT_NOT_ALLOW_LOGIN = 230;
    //代理不允许转账
    const AGENT_NOT_ALLOW_TRANSFER = 231;
    //代理不允许拉单
    const AGENT_NOT_ALLOW_ORDER = 232;

    /***
     * end==================================================================================
     */
    //充提点数为0
    const ZERO_BALANCE = 1000;
    //点数异常
    const ABNORMAL_BALANCE = 1001;
    //充提方式代码错误
    const INVALID_TRANSACTION_CODE = 1002;
    //提款点数大于玩家持有点数
    const WITHDRAWAL_AMOUNT_IS_GREATER_THAN_AVAILABLE_BALANCE = 1003;
    //交易对应单号过长
    const TRANSACTION_ID_IS_TOO_LONG = 1004;
    //交易对应单号重复
    const DUPLICATE_TRANSACTION_ID = 1005;
    //起始时间错误
    const INVALID_START_TIME = 1006;
    //结束时间错误
    const INVALID_END_TIME = 1007;
    //起始时间超前结束时间
    const START_TIME_EXCEEDS_END_TIME = 1008;
    //页码不是数字
    const PAGE_NUMBER_IS_NOT_A_NUMBER = 1009;
    //交易对应单号为空
    const TRANSACTION_ID_IS_EMPTY = 1010;
    //详细投注记录参数错误
    const INVALID_BET_RECORD_PARAMETERS = 1011;
    //交易单号不是数字
    const TRANSACTION_ID_IS_NOT_A_NUMBER = 1012;
    //对应单号不合法
    const INVALID_CORRESPONDING_ID = 1013;
    //此游戏编号未提供彩金模式
    const JACKPOT_MODE_IS_NOT_AVAILABLE_FOR_THIS_GAME = 1014;
    //未开放此语系
    const THIS_LANGUAGE_VERSION_IS_NOT_AVAILABLE = 1015;
    //已设定此语系
    const THIS_LANGUAGE_VERSION_HAS_BEEN_SET = 1016;
    //传入资料不是JSON
    const DATA_IS_NOT_IN_JSON_FORMAT = 1017;
    //资料笔数不是数字
    const NUMBER_OF_DATA_RECORDS_IS_NOT_A_NUMBER = 1018;
    //点数超过上限额度
    const AMOUNT_EXCEEDS_THE_UPPER_LIMIT = 1019;
    //搜寻时间超出最大范围
    const SEARCH_TIME_EXCEEDS_THE_MAXIMUM_RANGE = 1020;
    //交易单号不存在
    const TRANSACTION_ID_DOES_NOT_EXIST = 1021;
    //游戏类型错误
    const INVALID_GAME_TYPE = 1022;
    //页码超过总页数
    const PAGE_NUMBER_EXCEEDS_TOTAL_PAGES = 1023;
    //页码错误
    const INVALID_PAGE_NUMBER = 1024;
    //笔数错误
    const INVALID_RECORD_COUNT = 1025;
    //排序错误
    const INVALID_SORTING = 1026;
    //查询时间超过最小范围
    const SEARCH_TIME_EXCEEDS_THE_MINIMUM_RANGE = 1027;
    //无法取得商户密钥
    const FAILED_TO_GET_MERCHANT_KEY = 1028;
    //资料解密失败
    const FAILED_TO_DECRYPT_DATA = 1029;
    //商户签章比对失败
    const FAILED_TO_VERIFY_MERCHANT_SIGNATURE = 1030;
    //此商户代码禁用
    const MERCHANT_CODE_IS_DISABLED = 1031;
    //此方法禁用
    const METHOD_IS_DISABLED = 1032;
    //此商户代码的方法禁用
    const METHOD_IS_DISABLED_FOR_THIS_MERCHANT_CODE = 1033;
    //游戏不存在
    const GAME_DOES_NOT_EXIST = 1034;
    //游戏关闭
    const GAME_IS_CLOSED = 1035;
    //账号锁定中
    const ACCOUNT_IS_LOCKED = 1036;
    //游戏维护中
    const GAME_IS_UNDER_MAINTENANCE = 1037;
    //没有此账号的权限
    const NO_ACCESS_FOR_THIS_ACCOUNT = 1038;
    //不允许的IP
    const DISALLOWED_IP = 1039;
    //此方法维护中
    const METHOD_IS_UNDER_MAINTENANCE = 1040;
    //账号不存在
    const ACCOUNT_DOES_NOT_EXIST = 1041;
    //账号过长
    const ACCOUNT_IS_TOO_LONG = 1042;
    //账号重复
    const DUPLICATE_ACCOUNT = 1043;
    //账号在线
    const ACCOUNT_IS_ONLINE = 1044;
    //账号不在线
    const ACCOUNT_IS_OFFLINE = 1045;
    //账号过短
    const ACCOUNT_IS_TOO_SHORT = 1046;
    //URL是空白
    const URL_IS_BLANK = 1047;
    //商户响应为空
    const MERCHANT_RESPONSE_IS_EMPTY = 1048;
    //回传资料不是JSON格式
    const RETURNED_DATA_IS_NOT_IN_JSON_FORMAT = 1049;
    //验证失败
    const VERIFICATION_FAILED = 1050;
    //没有回传验证结果
    const NO_VERIFICATION_RESULT_IS_RETURNED = 1051;
    //取得平台点数非数字
    const PLATFORM_BALANCE_RETRIEVAL_FAILURE = 1052;
    //没有回传点数
    const NO_BALANCE_IS_RETURNED = 1053;
    //点数取得失败
    const FAILED_TO_RETRIEVE_BALANCE = 1054;
    //交易单写入失败
    const FAILED_TO_WRITE_TRANSACTION_RECORD = 1055;
    //充提错误
    const FAILED_TO_PERFORM_TOP_UP_OR_DOWN = 1056;
    //玩家信息查询空白
    const BLANK_PLAYER_INFORMATION_QUERY = 1057;
    //游戏记录总数查询空白
    const BLANK_GAME_RECORD_COUNT_QUERY = 1058;
    //游戏记录查询空白
    const BLANK_GAME_RECORD_QUERY = 1059;
    //交易记录总数查询空白
    const BLANK_TRANSACTION_RECORD_COUNT_QUERY = 1060;
    //查无资料
    const NO_DATA_IS_FOUND = 1061;
    //网址过期
    const URL_EXPIRED = 1062;
    //找不到游戏载体网址
    const GAME_CARRIER_URL_NOT_FOUND = 1063;
    //语系修改失败
    const LANGUAGE_VERSION_MODIFICATION_FAILED = 1064;
    //资料传输方式错误
    const INVALID_DATA_TRANSMISSION_METHOD = 1065;
    //无法抓取资料
    const FAILED_TO_RETRIEVE_DATA = 1066;
    //访问者IP过长
    const VISITOR_IP_IS_TOO_LONG = 1067;
    //无法预期的错误
    const UNEXPECTED_ERROR = 1068;
    //没有带入商户代码
    const MERCHANT_CODE_IS_MISSING = 1069;
    //没有带入币别
    const CURRENCY_IS_MISSING = 1070;
    //没有带入资料
    const DATA_IS_MISSING = 1071;
    //没有带入商户签章
    const USER_SIGN_IS_MISSING = 1072;
    //没有带入开始时间
    const BEGIN_TIME_IS_MISSING = 1073;
    //没有带入结束时间
    const ENDING_TIME_IS_MISSING = 1074;
    //没有带入页数
    const PAGE_IS_MISSING = 1075;
    //没有带入日期
    const DATE_TIME_IS_MISSING = 1076;
    //没有带入游戏类型
    const GAME_TYPE_IS_MISSING = 1077;
    //没有带入游戏记录编号
    const GAME_RECORD_TIME_IS_MISSING = 1078;
    //参数带入错误，请确认参数是否正确
    const PARAMS_IS_MISSING = 1079;

    //无效的token
    const INVALID_TOKEN = 1080;

    //無效的IP
    const INVALID_IP = 1081;

    //無效的時間格式
    const INVALID_TIME_FORMAT = 1082;

    //成功但內容物有失敗
    const SUCCESS_BUG_CONTENT_FAILED = 1083;

    //余额不足
    const INSUFFICIENT_BALANCE = 1084;

    //系统维护中
    const SYSTEM_MAINTENANCE = 1085;

    //資料格式錯誤
    const DATA_FORMAT_ERROR = 1086;

    //无法连线
    const UNABLE_TO_CONNECT = 1097;


}