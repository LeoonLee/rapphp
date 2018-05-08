<?php
/**
 * 南京灵衍信息科技有限公司
 * User: jinghao@duohuo.net
 * Date: 17/9/1
 * Time: 下午4:20
 */
namespace rap\web\mvc;

use rap\web\Request;
use rap\web\Response;

class AutoFindHandlerMapping implements HandlerMapping{

    public $separator="/";
    public $controllerDir="controller";
    public $controllerPostfix="Controller";
    public $prefixArr=[];

    public function prefix($prefix,$dir){
        $dir=str_replace('/','\\',$dir);
        $this->prefixArr[$prefix]=$dir;
    }

    public function map(Request $request, Response $response){
        $path=$request->path();
        $prefix="";
        $dir="";
        $find=false;
        foreach ($this->prefixArr as $toPrefix=>$toDir) {
            if(strpos($path, $toPrefix."/") === 0&&strlen($toPrefix)>strlen($prefix)){
                $prefix=$toPrefix;
                $dir=$toDir;
                $find=true;
            }
        }
        //没有找到为默认
        if(!$find){
            $prefix="/";
            $dir="app\\";
        }
        $path = substr($path,strlen($prefix));
        $path = str_replace($this->separator, '|', $path);
        $array=explode('|',$path);

        if(count($array)!=2){
            array_splice($array,count($array)-2,0,[$this->controllerDir]);
        }
        $method=array_pop($array);
        if(count($array)!=1) {
            $array[count($array)-1]=ucfirst($array[count($array)-1]);
            $classPath = $dir . implode('\\', $array) . $this->controllerPostfix;
        }else{
            $classPath = $dir . implode('\\', $array);
        }
        if(!class_exists($classPath)){
            return null;
        }
        $handlerAdapter=new ControllerHandlerAdapter($classPath,$method);
        $handlerAdapter->pattern($path);
        return $handlerAdapter;
    }

}