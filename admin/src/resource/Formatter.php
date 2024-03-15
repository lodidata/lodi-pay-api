<?php

declare(strict_types=1);

namespace Admin\src\resource;

class Formatter
{
    protected $resource;

    public function __construct($resource)
    {
        $this->setResource($resource);
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    public static function make($resource = null)
    {
        return new static($resource);
    }

    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function toArray(): array
    {
        if (is_object($this->resource)) {
            return (array) $this->resource;
        }

        if (is_array($this->resource)) {
            return $this->resource;
        }

        return ['value' => $this->resource];
    }
}
