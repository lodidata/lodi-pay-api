<?php

declare(strict_types=1);

namespace Admin\src\resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ModelFormatter extends ResponseFormatter
{
    protected $model = Model::class;

    public static function prune(Model $model): void
    {
    }

    public static function whenLoaded(Model $model, string $name)
    {
        if (! $model->relationLoaded($name)) {
            return;
        }

        $relation = $model->getRelation($name);

        if (is_null($relation)) {
            return;
        }

        if ($relation instanceof Model) {
            static::prune($relation);
        } elseif ($relation instanceof Collection) {
            foreach ($relation as $v) {
                static::prune($v);
            }
        }
    }

    public function getData()
    {
        if ($this->resource instanceof Model) {
            $this->validateModel($this->resource);
            static::prune($this->resource);

            return $this->resource->toArray();
        }

        if ($this->resource instanceof Collection) {
            $list = [];

            foreach ($this->resource as $v) {
                $this->validateModel($v);
                static::prune($v);

                $list[] = $v->toArray();
            }

            return compact('list');
        }

        if ($this->resource instanceof LengthAwarePaginator) {
            $list = [];

            foreach ($this->resource as $v) {
                $this->validateModel($v);
                static::prune($v);

                $list[] = $v->toArray();
            }

            $page = $this->resource->currentPage();
            $page_size = $this->resource->perPage();
            $total = $this->resource->total();

            return compact('list', 'page', 'page_size', 'total');
        }

        $class = static::class;

        throw new \Exception("[{$class}] 中 [{$this->resource}] 不是 [{$this->model}] 的实例或其集合");
    }

    protected function validateModel(Model $model)
    {
        if ($model instanceof $this->model) {
            return;
        }

        $class = static::class;

        throw new \Exception("[{$class}] 中 Model 不是 [{$this->model}] 的实例");
    }
}
