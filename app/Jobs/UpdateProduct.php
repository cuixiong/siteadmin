<?php
/**
 * php artisan make:job UpdateProduct
 */

namespace App\Jobs;

use App\Services\RabbitmqService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Admin\Http\Models\Site;


class UpdateProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected  $productKey;

    /**
     * UpdateProduct constructor.
     * @param $data
     * @throws \Exception
     */
    public function __construct($data)
    {

        $this->productKey = "product::info::{$data['id']}";
        if(is_array($data)){
            $data = json_encode($data);
        }
        //服务生产者
        RabbitmqService::push('product','exc_product','pus_product',$type='direct',$data);
    }

    /**
     * 服务消费者会走到这里，把消息消费掉
     * @throws \Exception
     */
    public function handle()
    {
        RabbitmqService::pop('product',function ($message){
            print_r('消费者消费消息'.PHP_EOL);

            print_r(PHP_EOL);

            $key = $this->productKey . ':' . date('Y-m-d H:i:s');

            $input = serialize(json_decode($message,true));

            $product = app('redis')->set($key,$input);
            $aa = Site::where('id',18)->update(['country_id'=>'1231']);
            if($product){
                print_r('消息消费成功');
                return true;
            }else{
                print_r('消息消费失败');
                return false;
            }
        });
    }

    /**
     * 异常扑获
     * @param \Exception $exception
     */
    public function failed(\Exception $exception){
        print_r($exception->getMessage());
    }
}

