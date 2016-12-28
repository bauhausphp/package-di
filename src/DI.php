<?php

namespace Bauhaus\DI;

use Bauhaus\Container\Container;
use Bauhaus\Container\ContainerItemNotFoundException;

class DI extends Container
{
    public function __construct(array $services = [])
    {
        foreach ($services as $label => $item) {
            if (!$item instanceof DIItem) {
                throw new \InvalidArgumentException(
                    "The item with label '$label' does not contain a DIItem"
                );
            }
        }

        parent::__construct($services);
    }

    public function get($label)
    {
        try {
            $item = parent::get($label);
        } catch (ContainerItemNotFoundException $e) {
            throw new DIServiceNotFoundException($label);
        }

        return $item->value();
    }

    public function all(): array
    {
        $arr = [];
        foreach ($this->items() as $label => $item) {
            $arr[$label] = $item->value();
        }

        return $arr;
    }

    public function withService(string $label, callable $service, $type = DIItem::SHARED): self
    {
        if ($this->has($label)) {
            throw new DIServiceAlreadyExistsException($label);
        }

        $services = $this->items();
        $services[$label] = new DIItem($service, $type);

        return new self($services);
    }

    public function withSharedService(string $label, callable $service): self
    {
        return $this->withService($label, $service, DIItem::SHARED);
    }

    public function withLazyService(string $label, callable $service): self
    {
        return $this->withService($label, $service, DIItem::LAZY);
    }

    public function withNotSharedService(string $label, callable $service): self
    {
        return $this->withService($label, $service, DIItem::NOT_SHARED);
    }

    private function items(): array
    {
        return parent::all();
    }
}