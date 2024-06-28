<?php
namespace Modules\Site\Http\Controllers;


use App\Http\Controllers\Controller;
use Modules\Site\Http\Models\ContactUs;
use Modules\Site\Http\Models\Order;

class PublicController extends Controller {
    public function getNoReadMsgCnt() {
        try{
            $data = [];
            $data['orderViewCnt'] = Order::query()->where('status', 0)->count();
            $data['contactUsViewCnt'] = ContactUs::query()->where('status', 0)->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        }catch (\Exception $e){
            ReturnJson(false, $e->getMessage());
        }

    }
}
