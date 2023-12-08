<?php
namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Notice;
use Modules\Admin\Http\Models\User;

class NoticeController extends CrudController{

    public function UserGetNotice(Request $request)
    {
        $notices = Notice::where('status',1)->select(['id','name','created_at','created_by'])->orderBy('sort','asc')->orderBy('created_at','desc')->get()->toArray();
        $user = User::find($request->user->id);
        $noticeIds = explode(',',$user->notice_ids);
        foreach ($notices as &$notice) {
            $notice['is_read'] = in_array($notice['id'],$noticeIds) ? 1 : 0;
        }
        ReturnJson(true,trans('lang.request_success'),$notices);
    }

    public function read(Request $request)
    {
        $notice = Notice::find($request->id);
        if (!$notice) {
            ReturnJson(false,'公告不存在');
        }
        $user = User::find($request->user->id);
        $noticeIds = $user->notice_ids ? explode(',',$user->notice_ids) : [];
        if (!in_array($notice['id'],$noticeIds)){
            $noticeIds[] = $notice['id'];
            $user->notice_ids = implode(',',$noticeIds);
            $user->save();
        }
        ReturnJson(true,trans('lang.request_success'),$notice);
    }

    public function NewsNotice(Request $request){
        $notice = Notice::where('status',1)->select(['id','name','created_at','created_by'])->orderBy('created_at','desc')->first();
        ReturnJson(true,trans('lang.request_success'),$notice);
    }
}