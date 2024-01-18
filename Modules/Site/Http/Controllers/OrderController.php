<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\ListStyle;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Pay;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\OrderGoods;
use Modules\Site\Http\Models\Products;
use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Models\EmailScene;
use Modules\Site\Http\Models\Email;
use Illuminate\Support\Facades\Mail;
use App\Mail\TrendsEmail;

class OrderController extends CrudController
{
    /**
     * 查询列表页
     * @param $request 请求信息
     * @param int $page 页码
     * @param int $pageSize 页数
     * @param Array $where 查询条件数组 默认空数组
     */
    protected function list(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            // 总数量
            $total = $model->count();
            // 查询偏移量
            if (!empty($request->pageNum) && !empty($request->pageSize)) {
                $model->offset(($request->pageNum - 1) * $request->pageSize);
            }
            // 查询条数
            if (!empty($request->pageSize)) {
                $model->limit($request->pageSize);
            }
            $model = $model->select($ModelInstance->ListSelect);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('id', $sort);
            }

            $record = $model->get();

            if ($record) {
                foreach ($record as $key => $item) {
                    $record[$key]['order_goods'] = [];
                    // $record[$key]['product_name'] = '';
                    $orderGoods = OrderGoods::query()->where('order_id', $item['id'])->get()->toArray();
                    if ($orderGoods) {
                        $record[$key]['order_goods'] = $orderGoods;
                        // $productIds = array_column($orderGoods, 'goods_id');
                        // $productNames = Products::query()->select('name')->whereIn('id', $productIds)->pluck('name')->toArray();
                        // $record[$key]['product_name'] = ($productNames && count($productNames)) ? implode("\n", $productNames) : '';
                    }
                }
            }

            //表头排序
            $headerTitle = (new ListStyle())->getHeaderTitle(class_basename($ModelInstance::class), $request->user->id);
            $data = [
                'total' => $total,
                'list' => $record,
                'headerTitle' => $headerTitle ?? [],
            ];
            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {

            //支付方式
            $data['pay_type'] = (new Pay())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);

            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }

            // 支付状态
            $data['pay_status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Pay_State', 'status' => 1], ['sort' => 'ASC']);

            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);


            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * AJax单行删除
     * @param $ids 主键ID
     */
    protected function destroy(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }

            $orderRecord = Order::query()->whereIn('id', $ids);
            if (!$orderRecord->delete()) {
                ReturnJson(FALSE, trans('lang.delete_error'));
            } else {
                $orderGoodsRecord = OrderGoods::query()->whereIn('order_id', $ids);
                $orderGoodsRecord->delete();
            }
            ReturnJson(TRUE, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 补发邮件
     * @param $id 主键ID
     */
    protected function sendOrderEmail(Request $request)
    {

        try {

            $scene = EmailScene::where('action','order')->select(['id','name','title','body','email_sender_id','email_recipient','status','alternate_email_id'])->first();
            if(empty($scene)){
                ReturnJson(FALSE,trans()->get('lang.eamail_error'));
            }

            if($scene->status == 0)
            {
                ReturnJson(FALSE,trans()->get('lang.eamail_error'));
            }
            $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene->email_sender_id);
            // 收件人的数组
            $emails = explode(',',$scene->email_recipient);
            // 邮箱账号配置信息
            $config = [
                'host' =>  $senderEmail->host,
                'port' =>  $senderEmail->port,
                'encryption' =>  $senderEmail->encryption,
                'username' =>  $senderEmail->email,
                'password' =>  $senderEmail->password
            ];
            $this->SetConfig($config);
            if($scene->alternate_email_id){
                // 备用邮箱配置信息
                $BackupSenderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene->alternate_email_id);
                $BackupConfig = [
                    'host' =>  $BackupSenderEmail->host,
                    'port' =>  $BackupSenderEmail->port,
                    'encryption' =>  $BackupSenderEmail->encryption,
                    'username' =>  $BackupSenderEmail->email,
                    'password' =>  $BackupSenderEmail->password
                ];
                $this->SetConfig($BackupConfig,'backups');// 若发送失败，则使用备用邮箱发送
            }

            foreach ($emails as $email) {
                try {
                    $this->SendEmail($email,$scene->body,[],$scene->title,$senderEmail->email);
                } catch (\Exception $e) {
                }
            }
            ReturnJson(true,trans()->get('lang.eamail_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
        try {
            $record = $this->ModelInstance()->findOrFail($request->id);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
    
    /**
     * 发送邮箱
     * @param string $email 接收邮箱号
     * @param string $templet 邮箱字符串的模板
     * @param array $data 渲染模板需要的数据
     * @param string $subject 邮箱标题
     * @param string $EmailUser 邮箱发件人
     */
    private function SendEmail($email,$templet,$data,$subject,$EmailUser,$name = 'trends')
    {
        $res = Mail::mailer($name)->to($email)->send(new TrendsEmail($templet,$data,$subject,$EmailUser));
        return $res;
    }
}
