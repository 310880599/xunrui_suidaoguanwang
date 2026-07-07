<?php namespace Phpcmf\Controllers;

use think\facade\Db;

class Tp extends \Phpcmf\Common
{

    public function index() {

        // 连接cms2数据库
        
        $dbdef = \Config\Database::connect('default', false);
        
        $db2 = \Config\Database::connect('cms2', false);
        
        
        
        //var_dump($dbdef);
        
        // 通过db2查询 cms2的member表数据
        
        //$rt = $db2->table("1_xinwenzhongxin")->where("catid", "17")->get()->getResultArray();
        
        //$rt = $db2->table("1_xinwenzhongxin")->where("catid", "17")->get();
        
        //print_r($rt);
        
       
        //$result = Db::name('1_xinwenzhongxin')->replace()->insertAll($rt);
        
        
        //$rtcon = $db2->table("1_xinwenzhongxin_data_0")->where("catid", "17")->get()->getResultArray();
        
        //var_dump($rtcon);
        
        
        //$resultcon = Db::name('1_xinwenzhongxin_data_0')->replace()->insertAll($rtcon);
        
        
    }

}