<?php

namespace Maya\FilamentSaasPlugin\Services\Contracts;

class Subscriber
{
    public string $name;
    public string $model;

    public static function make(string $name): self
    {
        return (new self)->name($name);
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function model(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'model' => $this->model
        ];
    }
}
