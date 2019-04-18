<?php

class A
{
    public function sayHello()
    {
        echo 'hello';
    }
}
$t =  stream_context_create(array(
    'http'=>array(
        'h' => 1
    )
));
//set_error_handler(function($code, $msg, $file, $line){
//   echo $msg.$file.$line;
//});
echo 'start';
sleep(10);
/**
 * Created by PhpStorm.
 * User: jamsonzan
 * Date: 2019/4/17
 * Time: 9:23
 */