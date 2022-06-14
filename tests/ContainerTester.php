<?php

require __DIR__ . "/../src/ContainerExceptionImpl.php";
require __DIR__ . "/../src/ContainerImpl.php";
require __DIR__ . "/../src/NotFoundExceptionImpl.php";

class ContainerTester extends \PHPUnit\Framework\TestCase
{

    public function testContainer(): void
    {
        $container = new ContainerImpl();
        $task = $container->get(Task::class);

        $current = "";
        if (is_file("container.test")) {
            $current = file_get_contents("container.test");
        }
        $current = $current . PHP_EOL . serialize($task);
        file_put_contents("container.test", $current);

        sleep(5);


        $task = $container->get(Task::class);
        $current = "";
        if (is_file("container.test")) {
            $current = file_get_contents("container.test");
        }
        $current = $current . PHP_EOL . serialize($task);

        file_put_contents("container.test", $current);




        $task = $container->get(Logger::class);
        $current = "";
        if (is_file("container.test")) {
            $current = file_get_contents("container.test");
        }
        $current = $current . PHP_EOL . serialize($task);

        file_put_contents("container.test", $current);
    }
}

class Task
{
    private string $file = "container.test";
    private $time;

    public function __construct(Logger $loger)
    {
        $this->time = (new DateTime)->getTimestamp();
        $current = "";
        if (is_file($this->file)) {
            $current = file_get_contents($this->file);
        }
        $current = $current . PHP_EOL . ' Task created successfully ' . $this->time;

        file_put_contents($this->file, $current);
    }
}

class Logger
{
    private string $file = "container.test";

    public function __construct(DB $database, DB2 $DB2)
    {
        $current = "";
        if (is_file($this->file)) {
            $current = file_get_contents($this->file);
        }
        $current = $current . PHP_EOL . ' Logger created successfully ';

        file_put_contents($this->file, $current);
    }
}

class DB
{
    private string $file = "container.test";

    public function __construct()
    {
        $current = "";
        if (is_file($this->file)) {
            $current = file_get_contents($this->file);
        }
        $current = $current . PHP_EOL . ' DB created successfully ';

        file_put_contents($this->file, $current);
    }
}

class DB2
{
    private string $file = "container.test";

    public function __construct()
    {
        $current = "";
        if (is_file($this->file)) {
            $current = file_get_contents($this->file);
        }
        $current = $current . PHP_EOL . ' DB2 created successfully ';

        file_put_contents($this->file, $current);
    }
}