<?php

namespace App\Console\Commands;

use Foolz\SphinxQL\Drivers\Mysqli\Connection;
use Foolz\SphinxQL\SphinxQL;
use Illuminate\Console\Command;
use SphinxClient;

class Test2Command extends Command {
    protected $signature = 'testsphinx';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        try {
            //$conn = mysqli_connect("127.0.0.1:9306", "", "", "");
//            $conn = mysqli_connect("8.134.152.191:9306", "", "", "");
//            if (mysqli_connect_errno()) {
//                $isSphinx = false;
//            }
//            $sql = "select * FROM `products_rt` limit 10";
//            $productListRes = mysqli_query($conn, $sql);//返回数据，没数据显示{}，语句报错显示false，无提示
//            while ($row = mysqli_fetch_assoc($productListRes)) {
//                var_dump($row);
//            }
            $start_time = microtime(true);
            $conn = new Connection();
            //$conn->setParams(array('host' => '8.134.152.191', 'port' => 9306));
            $conn->setParams(array('host' => '8.219.5.215', 'port' => 9306));
//            $query = (new SphinxQL($conn))->select('*')
//                                          ->from('products_rt')
//                                          ->where('status', 'between', [0 , 10000])
//                                          ->limit(0, 10);
//            $result = $query->execute();
//            $a = $result->fetchAllAssoc();
            //新增
//            $data = [
//                'id'   => 798396657,
//                'name' => '崔志雄111'
//            ];
//            $res = (new SphinxQL($conn))->insert()->into('products_rt')->set($data)->execute();
            //修改
            $updData = [
                'name' => 'cuizhixiong1112222'
            ];
            $res = (new SphinxQL($conn))->update('products_rt')->where("id", 798396657)->set($updData)->execute();
            //删除
//            $res = (new SphinxQL($conn))->delete()->from('products_rt')->where("id" , 798396655)->execute();
            $cnt = $res->getAffectedRows();
            var_dump($cnt);
            dump("开始时间".$start_time);
            dump("结束时间".microtime(true));
            die;
            //dd($a);
        } catch (\Throwable $th) {
            var_dump($th->getMessage());
            $isSphinx = false;
        }
        var_dump(1);
        die;
    }
}
