<?php

declare(strict_types=1);

namespace Admin\src\resource;

use Logic\Define\StateMsg;
use Logic\Define\State;

class ResponseFormatter extends Formatter
{
    protected $code;

    protected $message;

    public function setCode(?int $code = null): self
    {
        $this->code = $code;

        return $this;
    }

    public function setMessage(?string $message = null): self
    {
        $this->message = $message;

        return $this;
    }

    public function getCode(): int
    {
        $codes = array_keys(StateMsg::$msgArr);
        return is_int($this->code)
            && in_array($this->code, $codes)
            ? $this->code
            : State::SUCCESS;
    }

    public function getMessage(): string
    {
        $code = $this->getCode();
        $msg = StateMsg::getMsg($code);

        if (is_string($this->message) && $this->message) {
            $msg .= '：' . $this->message;
        }

        return $msg . '。';
    }

    public function getData()
    {
        if (is_object($this->resource)) {
            return $this->resource;
        }

        if (is_array($this->resource)) {
            return (object) $this->resource;
        }

        if (is_null($this->resource)) {
            return new \StdClass();
        }

        return (object) ['value' => $this->resource];
    }

    public function toArray(): array
    {
        $result = $this->getData();
        $attributes = [];
        if(isset($result['page'])){
            $attributes['page'] = $result['page'];
        }
        if(isset($result['page_size'])){
            $attributes['page_size'] = $result['page_size'];
        }
        if(isset($result['total'])){
            $attributes['total'] = $result['total'];
        }
        if (isset($result['list'])){
            $result =  $result['list'];
        }
        return [
            'state' => $this->getCode(),
            'message' => $this->getMessage(),
            'data' => $result,
            'attributes' => $attributes
        ];
    }
}
