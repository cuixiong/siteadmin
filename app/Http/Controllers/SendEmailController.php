<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TrendsEmail;
use Illuminate\Support\Facades\Config;
use Modules\Admin\Http\Models\Email;
use Modules\Admin\Http\Models\EmailScene;
use Modules\Admin\Http\Models\User;

class SendEmailController extends Controller
{
    /**
     * 动态配置邮箱参数
     * @param array $data 邮箱配置参数信息
     */
    private function SetConfig($data){
        $keys = ['transport','host','port','encryption','username','password','timeout','local_domain'];
        foreach ($data as $key => $value) {
            if(in_array($key,$keys)){
                Config::set('mail.mailers.trends.'.$key,$value,true);
            }
        }
        return true;
    }

    /**
     * 发送邮箱
     * @param string $email 接收邮箱号
     * @param string $templet 邮箱字符串的模板
     * @param array $data 渲染模板需要的数据
     * @param string $subject 邮箱标题
     * @param string $EmailUser 邮箱发件人
     */
    private function SendEmail($email,$templet,$data,$subject,$EmailUser)
    {
        // 发送邮件
        Mail::mailer('trends')->to($email)->send(new TrendsEmail($templet,$data,$subject,$EmailUser));
    }

    /**
     * 注册场景的发邮请求
     * @param use Illuminate\Http\Request;
     * @return response Code
     */
    public function register(Request $request)
    {
        try {
            if(!isset($request->user_id) || empty($request->user_id)){
                ReturnJson(FALSE,'缺少账号ID');
            }
            $id = $request->user_id;
            $user = User::find($id);
            $user = $user ? $user->toArray() : [];
            $scene = EmailScene::select(['name','title','body','email_sender_id','email_recipient','status'])->find(1);
            if(empty($scene)){
                ReturnJson(FALSE,'邮箱场景不存在，无法发送');
            }
            if($scene->status == 0)
            {
                ReturnJson(FALSE,'邮箱场景已被禁用，无法发送');
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
            foreach ($emails as $email) {
                $this->SendEmail($email,$scene->body,$user,$scene->title,$senderEmail->email);
            }
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
