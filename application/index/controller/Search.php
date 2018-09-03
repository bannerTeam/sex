<?php
namespace app\index\controller;
use think\Controller;

class Search extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $tables = model('Art')->query('SELECT * FROM information_schema.tables where table_schema=\'maccms10\' order by TABLE_NAME');
        $list = [];

        foreach($tables as $k=>$v){
            $tb_name = $v['TABLE_NAME'];
            $tb_comment = $v['TABLE_COMMENT'];

            $list[$k]['tb_name'] =  $tb_name;
            $list[$k]['tb_comment'] =  $tb_comment;

            $columns = model('Art')->query('SELECT * FROM information_schema.columns where table_name=\''.$tb_name.'\'  ');


            $list[$k]['columns'] = $columns;

            foreach ($columns as $k2=>$v2){
                echo '{$vo.'. $v2['COLUMN_NAME']. '} '. $v2['COLUMN_COMMENT']  .'<br>';
            }

            echo '<br><br><br>';
        }

    }

}
