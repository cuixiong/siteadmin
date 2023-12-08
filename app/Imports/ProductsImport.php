<?php

namespace App\Imports;

use Modules\Site\Http\Models\Products;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\ProductsDescription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Services\RabbitmqService;

class ProductsImport implements ToCollection, WithChunkReading
{
    public $site = '';
    public $log_id = '';
    
    private $skipFirstRow = true;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $collection)
    {
        // ini_set('memory_limit', '4096M');
        if ($this->skipFirstRow) {
            // 在第一个分块中移除标题行
            // $collection = $collection->shift();
            $collection = $collection->skip(1);
            $this->skipFirstRow = false;
        }
        if (count($collection) > 0) {
            $data = [
                'class' => 'Modules\Site\Http\Controllers\ProductsController',
                'method' => 'handleProducts',
                'site' => $this->site,
                'log_id' => $this->log_id,
                'data' => $collection
            ];
            $data = json_encode($data);
            $RabbitMQ = new RabbitmqService();
            $RabbitMQ->setQueueName('products-queue'); // 设置队列名称
            $RabbitMQ->setExchangeName('Products'); // 设置交换机名称
            $RabbitMQ->setQueueMode('fanout'); // 设置队列模式
            $RabbitMQ->push($data); // 推送数据
        }
    }

    public function chunkSize(): int
    {
        // return 100;
        return 100;
    }
}
