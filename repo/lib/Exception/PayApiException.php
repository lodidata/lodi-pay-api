<?php
namespace Lib\Exception;

use Logic\Define\State;
use Logic\Define\StateMsg;

class PayApiException extends \Exception
{
    /**
     * @param int $code
     * @param null $message
     */
    public function __construct( int $code ,$message = null )
    {
        $code = $code ?: State::SYSTEM_ERROR;
        $message = ( $message ?: StateMsg::getMsg($code) ) ?? StateMsg::getMsg(State::SYSTEM_ERROR);
        $this->message = $message;
        $this->code = $code;
    }

}