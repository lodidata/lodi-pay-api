<?php

declare (strict_types=1);
namespace Model\filter;
use Illuminate\Support\Arr;
abstract class BaseFilter {
    /**
     * Array of input to filter.
     *
     * @var array
     */
    protected $input;
    protected $query;

    public $preload = [];
    public $relations = [];


    /**
     * ModelFilter constructor.
     *
     * @param $query
     * @param array $input
     * @param bool $relationsEnabled
     */
    public function __construct($query, $input, $relationsEnabled = true)
    {
        $this->query = $query;
        $this->input = $this->removeEmptyInput($input);
    }

    public function handle() {
        if ($this->preload) {
            foreach ($this->preload as $method) {
                if(isset($this->input[$method])) {
                    if (method_exists($this, $method)) {
                        $this->{$method}(Arr::get($this->input, $method));
                    }
                }
            }
    
            Arr::forget($this->input, $this->preload);
        }
        
        $relationFields = Arr::flatten($this->relations);
        foreach ($this->input as $method => $val) {
            if (in_array($method, $relationFields)) continue;

            if (method_exists($this, $method)) {
                $this->{$method}($this->query, $val);
                unset($this->input[$method]);
            }
        }

        $filters = array_keys($this->input);

        foreach ($this->relations as $related => $fields) {
            $fields = array_intersect($fields, $filters);
            if ($fields) {
                $this->query->whereHas($related, function($query) use($fields) {
                    foreach ($fields as $method) {
                        $val = Arr::get($this->input, $method);
    
                        if (method_exists($this, $method)) {
                            $this->{$method}($query, $val);
                        }
                    }
                });
            }
            
        }
    }

    /**
     * Retrieve input by key or all input as array.
     *
     * @param null $key
     * @param null $default
     * @return array|mixed|null
     */
    public function input($key = null, $default = null)
    {
        if ($key === null) {
            return $this->input;
        }

        return array_key_exists($key, $this->input) ? $this->input[$key] : $default;
    }

    /**
     * Remove empty strings from the input array.
     *
     * @param array $input
     * @return array
     */
    public function removeEmptyInput($input)
    {
        $filterableInput = [];

        foreach ($input as $key => $val) {
            if ($val !== '' && $val !== null) {
                $filterableInput[$key] = $val;
            }
        }

        return $filterableInput;
    }
}
