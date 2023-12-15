<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Modules\Site\Http\Models\Products;

class ProductsExport implements FromQuery
{

    use Exportable;
    // /**
    //  * @return \Illuminate\Support\Array
    //  */
    // public function array(): array
    // {
    //     $title = ['id', 'name', 'english_name', 'publisher_id', 'category_id', 'country_id', 'price', 'keywords', 'url', 'published_date'];
    //     $list = Products::select($title)->get()->toArray();
    //     array_unshift($list, $title);
    //     return $list;
    // }
    public function query()
    {
        return Products::query();
    }
}
