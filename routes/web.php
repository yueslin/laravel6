<?php


interface Milldeware
{
    public static function handle(Closure $next);
}

class VerfiyCsrfToken implements Milldeware
{
    public static function handle(Closure $next)
    {
        echo "验证 csrf Token<br/>";
        $next();
    }
}

class VerfiyAuth implements Milldeware {

    public static function handle(Closure $next)
    {
        echo '验证是否登录 <br>';
        $next();
    }
}

class SetCookie implements Milldeware
{
    public static function handle(Closure $next)
    {
        $next();
        echo "设置cookie信息<br/>";
    }
}

function call_middware_1() {
    SetCookie::handle(function (){
        VerfiyAuth::handle(function() {
            $handle = function() {
                echo '当前要执行的程序!';
            };
            VerfiyCsrfToken::handle($handle);
        });
    });
}

function call_middware()
{
    $handle = function (){
        echo "当前要执行的程序!<br/>";
    };

    $pipe_arr = [
        'VerfiyAuth',
        'VerfiyCsrfToken',
        'SetCookie',
    ];

    //用回调函数迭代地将数组简化为单一的值
    /**
     *
     * array 输入的数组
     * callback
     *     carry  携带上次迭代里处理出的值，如果本次迭代是第一次，那么这个值是 initial
     *     item   携带了本次迭代的值
     * initial    如果指定了可选参数 initial，该参数将在处理开始前使用，
     *            或者当处理结束，数组为空时的最后一个结果。
     *
     *
     */
    // 最后形成一个包含所有前面循环闭包的闭包函数 例
//    SetCookie::handle(function (){
//        VerfiyCsrfToken::handle(function() {
//            $handle = function() {
//                echo '当前要执行的程序!';
//            };
//            VerfiyAuth::handle($handle);
//        });
//    });

    $callback = array_reduce($pipe_arr,function ($stack,$pipe){
        echo $pipe."<br/>";
        return function ()use($stack,$pipe){
            return $pipe::handle($stack);
        };
    },$handle);

    // 把第一个参数作为回调函数调用
    call_user_func($callback);
}

class test2
{
    public function echo2()
    {
        echo "test:call_user_func";
    }
}

Route::get('/', function () {

    call_middware();

});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
