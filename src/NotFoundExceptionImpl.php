<?php


use Psr\Container\NotFoundExceptionInterface;

class NotFoundExceptionImpl extends InvalidArgumentException implements NotFoundExceptionInterface
{

}