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

class ProductsImport implements ToCollection
// , WithChunkReading, ShouldQueue
{

    public $header = [];

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $collection)
    {
        //跳过标题
        $collection = $collection->skip(1);
        // // file_put_contents("C:\\Users\\Administrator\\Desktop\\bbbbb.txt",count($collection),FILE_APPEND );
        // if (count($collection) > 0) {
        //     $data = json_encode(['class' => 'Modules\Site\Http\Controllers\ProductsController', 'method' => 'handleProducts', 'data' => $collection]);
        //     $RabbitMQ = new RabbitmqService();
        //     $RabbitMQ->setQueueName('products-queue'); // 设置队列名称
        //     $RabbitMQ->setExchangeName('Products'); // 设置交换机名称
        //     $RabbitMQ->setQueueMode('fanout'); // 设置队列模式
        //     $RabbitMQ->push($data); // 推送数据
        // }
        foreach ($collection as $row) {

            //表头
            $item = [];
            $item['name'] = $row[0];
            $item['pages'] = $row[1];
            $item['tables'] = $row[2];
            $item['price'] = $row[3];
            $item['published_date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($row[6]);
            $item['category_id'] = ProductsCategory::where('name', trim($row[13]))->value('id') ?? 0;
            $item['author'] = $row[14];
            $item['keywords'] = $row[27];

            $itemDescription = [];
            $itemDescription['description'] = str_replace('_x000D_', '', $row[9]);
            $itemDescription['table_of_content'] = str_replace('_x000D_', '', $row[11]);
            $itemDescription['tables_and_figures'] = str_replace('_x000D_', '', $row[12]);
            $itemDescription['companies_mentioned'] = str_replace('_x000D_', '', $row[17]);

            //新纪录年份
            $newYear = Products::publishedDateFormatYear($item['published_date']);

            // 处理每行数据
            $product = Products::where('name', trim($row[0]))->first();
            if ($product) {
                //旧纪录年份
                $oldPublishedDate = $product->published_date;
                $oldYear = Products::publishedDateFormatYear($oldPublishedDate);
                //更新报告
                $product->update($item);

                $newProductDescription = (new ProductsDescription($newYear));
                //出版时间年份更改
                if ($oldYear != $newYear) {
                    //删除旧详情
                    if ($oldYear) {
                        $oldProductDescription = (new ProductsDescription($oldYear))->where('product_id', $product->id)->first();
                        $oldProductDescription->delete();
                    }
                    //然后新增
                    $descriptionRecord = $newProductDescription->saveWithAttributes($itemDescription);
                } else {
                    //直接更新
                    $newProductDescription = $newProductDescription->where('product_id', $product->id)->first();
                    $descriptionRecord = $newProductDescription->updateWithAttributes($itemDescription);
                }
            } else {
                //新增报告
                $product = Products::create($item);
                //新增报告详情
                $newProductDescription = (new ProductsDescription($newYear));
                $itemDescription['product_id'] = $product->id;
                $descriptionRecord = $newProductDescription->saveWithAttributes($itemDescription);
            }
        }

    }


    // public function chunkSize(): int
    // {
    //     return 10;
    // }
}
