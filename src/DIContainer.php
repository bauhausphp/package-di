<?php

namespace Bauhaus;

use Bauhaus\Container;
use Bauhaus\Container\Factory;
use Bauhaus\DIContainer\Service;
use Bauhaus\DIContainer\ServiceType;
use Bauhaus\DIContainer\ServiceNotFoundException;
use Bauhaus\DIContainer\ServiceAlreadyRegisteredException;

class DIContainer extends Container
{
    public function get($name)
    {
        $service = parent::get($name);

        return $service->value();
    }

    public function asArray(): array
    {
        $arr = [];
        foreach ($this->items() as $name => $service) {
            $arr[$name] = $service->value();
        }

        return $arr;
    }

    public function withService(string $name, callable $callable, $type = ServiceType::SHARED): self
    {
        if ($this->has($name)) {
            throw new ServiceAlreadyRegisteredException($name);
        }

        $factory = new Factory($this);
        $service = new Service($callable, $type);

        return $factory->containerWithItemAdded($name, $service);
    }

    public function withSharedService(string $name, callable $service): self
    {
        return $this->withService($name, $service, ServiceType::SHARED);
    }

    public function withLazyService(string $name, callable $service): self
    {
        return $this->withService($name, $service, ServiceType::LAZY);
    }

    public function withNotSharedService(string $name, callable $service): self
    {
        return $this->withService($name, $service, ServiceType::NOT_SHARED);
    }

    protected function canContain($service): bool
    {
        return $service instanceof Service;
    }

    protected function itemCanNotBeContainedExceptionMessage(string $name): string
    {
        return "The service '$name' is not an instance of Bauhaus\DI\Service";
    }

    protected function itemNotFoundHandler(string $name)
    {
        throw new ServiceNotFoundException($name);
    }
}
