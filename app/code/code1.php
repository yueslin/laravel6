<?php

interface Log
{
    public function write();
}

// 文件记录日志
class FileLog implements Log
{
    public function write(){
        echo 'file log write...';
    }
}

// 数据库记录日志
class DatabaseLog implements Log
{
    public function write(){
        echo 'database log write...';
    }
}

class User
{
    protected $log;
    public function __construct(Log $log)
    {
        $this->log = $log;
    }
    public function login()
    {
        // 登录成功，记录登录日志
        echo 'login success...';
        $this->log->write();
    }
}

/**
 * Class Ioc 容器
 */
class Ioc
{
    public $binding = [];

    public function bind($abstract,$concrete)
    {
        //这里为什么要返回一个closure呢？
        //因为bind的时候还不需要创建User对象，
        //所以采用closure等make的时候再创建FileLog;
        $this->binding[$abstract]['concrete'] = function ($ioc) use ($concrete) {
            return $ioc->build($concrete);
        };
    }

    public function make($abstract)
    {
        // 根据key 获取 binding 的值
        $concrete = $this->binding[$abstract]['concrete'];
        return $concrete($this);
    }

    function build($concrete)
    {
        //获取反射类
        $reflector  = new ReflectionClass($concrete);
        //获取构造函数
        $constructor = $reflector->getConstructor();
        // 为什么这样写的? 主要是递归。比如创建FileLog不需要传入参数。
        if (is_null($constructor)) {
            return $reflector->newInstance();
        }else{
            // 构造函数依赖的参数
            $paramters = $constructor->getParameters();
            // 根据参数返回实例，如 FileLog
            $dependencies = [];
            foreach ($paramters as $paramter){
                $dependencies[] = $this->make($paramter->getClass()->name);
            }
            return $reflector->newInstanceArgs($dependencies);
        }
    }

}

/**
 * Class UserFacade 门脸模式
 */
class UserFacade
{

    protected static $ioc;

    public static function setFacadeIoc($ioc)
    {
        static::$ioc = $ioc;
    }

    // 返回User在Ioc中的bind的key
    protected static function getFacadeAccessor()
    {
        return 'user';
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = static::$ioc->make(static::getFacadeAccessor());

        return $instance->$name(...$arguments);
    }

}

//实例化IoC容器
$ioc = new Ioc();
$ioc->bind('Log','FileLog');
$ioc->bind('user','User');

UserFacade::setFacadeIoc($ioc);
UserFacade::login('hello','test');
//    $user = $ioc->make('user');
//    $user->login();
