<?php


use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerImpl implements ContainerInterface
{
    /**
     * Store all entries in the container
     */
    protected array $container = array();

    /**
     * Return an instance from the container by its identifier.
     *
     * @param string $id Identifier of the entry to look for.
     * @return mixed Instance.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function get(string $id): object
    {
        $has = $this->container[$id] ?? null;
        if ($has === null) {
            return $this->resolve($id);
        } else {
            return $this->container[$id];
        }
    }

    /**
     * Returns true if the container can give an instance for the given identifier.
     * Returns false otherwise.
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public function has(string $id) : bool
    {
        try {
            $this->resolve($id);
        } catch (Throwable $ignored) {}
        return isset($this->container[$id]);
    }

    /**
     * @param string $id
     * @param $instance
     * @return mixed
     */
    public function set(string $id, $instance)
    {
        $this->container[$id] = $instance;
        return $instance;
    }


    /**
     * Resolves a class name and creates its instance with dependencies
     * @param $id
     * @return object The resolved instance
     */
    protected function resolve($id): object
    {
        $reflector = $this->getReflector($id);
        $constructor = $reflector->getConstructor();
        if ($reflector->isInterface()) {
            return $this->set($id, $this->resolveInterface($reflector));
        }
        if (!$reflector->isInstantiable()) {
            throw new ContainerExceptionImpl(
                "Cannot inject {$reflector->getName()} to {$id} because it cannot be instantiated"
            );
        }
        if (null === $constructor) {
            return $this->set($id, $reflector->newInstance());
        }
        $args = $this->getArguments($constructor);
        return $this->set($id, $reflector->newInstanceArgs($args));
    }

    protected function getReflector($id): ReflectionClass
    {
        try {
            return new ReflectionClass($id);
        } catch (\ReflectionException $e) {
            throw new NotFoundExceptionImpl(
                $e->getMessage(), $e->getCode()
            );
        }
    }


    //Не работает для кольцевых зависимостей
    /**
     * Get the constructor arguments of a class
     * @param ReflectionMethod $constructor The constructor
     * @return array The arguments
     */
    protected function getArguments(\ReflectionMethod $constructor): array
    {
        $args = [];
        $params = $constructor->getParameters();
        foreach ($params as $param) {
            if ($param->getClass() !== null) {
                $args[] = $this->get($param->getClass()->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            }
        }
        return $args;
    }

    /**
     * @param ReflectionClass $reflector The interface Reflector
     * @return object First instance implementing the interface that found
     */
    protected function resolveInterface(ReflectionClass $reflector): object
    {
        $classes = get_declared_classes();

        foreach ($classes as $class) {
            $rf = $this->getReflector($class);
            if ($rf->implementsInterface($reflector->getName())) {
                return $this->get($rf->getName());
            }
        }
        throw new NotFoundExceptionImpl(
            "Class {$reflector->getName()} not found", 1
        );
    }

}