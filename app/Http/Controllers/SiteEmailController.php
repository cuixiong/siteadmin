<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TrendsEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Modules\Admin\Http\Models\AliyunOssConfig;
use Modules\Admin\Http\Models\City;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\ContactUs;
use Modules\Site\Http\Models\Email;
use Modules\Site\Http\Models\EmailLog;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\User;
use Modules\Site\Http\Models\EmailScene;
use Modules\Site\Http\Models\SystemValue;

class SiteEmailController extends Controller
{
    // 注册发邮method
    private $EmailCodes = ['register' => '注册账号'];
    /**
     * 动态配置邮箱参数
     * @param array $data 邮箱配置参数信息
     */
    private function SetConfig($data,$name = 'trends'){
        $keys = ['transport','host','port','encryption','username','password','timeout','local_domain'];
        foreach ($data as $key => $value) {
            if(in_array($key,$keys)){
                Config::set('mail.mailers.'.$name.'.'.$key,$value,true);
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
    private function SendEmail($email,$templet,$data,$subject,$EmailUser,$name = 'trends')
    {
        $res = Mail::mailer($name)->to($email)->send(new TrendsEmail($templet,$data,$subject,$EmailUser));
        return $res;
    }

    /**
     * 邮件测试发送中转方法
     * @param use Illuminate\Http\Request;
     */
    public function test(Request $request)
    {
        try {
            // 验证表单数据
            $this->validatorData($request->all());
            $action = $request->action.'Test';
            // 调用
            $res = $this->$action($request);
            $res ? ReturnJson(true,trans()->get('lang.eamail_success')) : ReturnJson(FALSE,trans()->get('lang.eamail_error')); 
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * 邮箱测试发送表单数据验证
     * @param array $data;
     */
    private function validatorData($data)
    {
        $rules = [
            'name' => 'required',
            'title' => 'required',
            'body' => 'required',
            'email_sender_id' => 'required',
            'email_recipient' => 'required',
            'action' => 'required',
        ];
        $message = [
            'name.required' => '场景名称不能为空',
            'title.required' => '邮箱标题不能为空',
            'body.required' => '邮箱内容不能为空',
            'email_sender_id.required' => '发送邮件的邮箱ID不能为空',
            'email_recipient.required' => '邮箱收件人不能为空',
            'action.required' => '测试邮箱的code方法不能为空',
        ];
        $validator = Validator::make($data, $rules,$message);
        if ($validator->fails()) {
            $errors = $validator->errors()->messages();
            $errors = array_shift($errors['action']);
            ReturnJson(FALSE,$errors);
        }
    }

    /**
     * return email method
     */
    public function EmailCode(){
        $list = [];
        if(empty($this->EmailCodes)){
            ReturnJson(true,'',$list);
        }
        foreach ($this->EmailCodes as $key => $value) {
            $list[] = [
                'value' => $key,
                'label' => "$value($key)"
            ];
        }
        ReturnJson(true,'',$list);
    }

    /**
     * 注册场景的发邮请求
     * @param use Illuminate\Http\Request;
     * @return response Code
     */
    public function register($id)
    {
        try {
            $user = User::find($id);
            $user = $user ? $user->toArray() : [];
            $token = $user['email'].'&'.$user['id'];
            $user['token'] = base64_encode($token);
            $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
            $scene = EmailScene::where('action','register')->select(['id','name','title','body','email_sender_id','email_recipient','status','alternate_email_id'])->first();
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
                    $this->SendEmail($email,$scene->body,$user,$scene->title,$senderEmail->email);
                } catch (\Exception $e) {
                    if($scene->alternate_email_id){
                        $this->SendEmail($email,$scene->body,$user,$scene->title,$BackupSenderEmail->email,'backups');
                    }
                }
            }
            EmailLog::AddLog(1,$scene->email_sender_id,$emails,$scene->id,$user);
            ReturnJson(true,trans()->get('lang.eamail_success'));
        } catch (\Exception $e) {
            EmailLog::AddLog(0,$scene->email_sender_id,$emails,$scene->id,$user);
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * 注册场景的测试请求
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    private function registerTest($request)
    {
        $id = $request->user->id;
        $user = User::find($id);
        $user = $user ? $user->toArray() : [];
        $token = $user['email'].'&'.$user['id'];
        $user['token'] = base64_encode($token);
        $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $scene = $request->all();
        $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene['email_sender_id']);
        // 收件人的数组
        $emails = explode(',',$scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host' =>  $senderEmail->host,
            'port' =>  $senderEmail->port,
            'encryption' =>  $senderEmail->encryption,
            'username' =>  $senderEmail->email,
            'password' =>  $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email,$scene['body'],$user,$scene['title'],$senderEmail->email);
        return true;
    }

    /**
     * reset password eamil send
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    public function password(Request $request){
        try {
            if(!isset($request->email) || empty($request->email)){
                ReturnJson(FALSE,trans()->get('lang.eamail_empaty'));
            }   
            $email = $request->email;
            $user = User::where('email',$email)->first();
            if(empty($user)){
                ReturnJson(FALSE,trans()->get('lang.eamail_undefined'));
            }
            $user = $user->toArray();
            $token = $user['email'].'&'.$user['id'];
            $user['token'] = base64_encode($token);
            $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
            $scene = EmailScene::where('action','password')->select(['id','name','title','body','email_sender_id','email_recipient','status','alternate_email_id'])->first();
            if(empty($scene)){
                ReturnJson(FALSE,trans()->get('lang.eamail_error'));
            }
            $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene->email_sender_id);
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
            try {
                $this->SendEmail($email,$scene->body,$user,$scene->title,$senderEmail->email);
            } catch (\Exception $e) {
                if($scene->alternate_email_id){
                    $this->SendEmail($email,$scene->body,$user,$scene->title,$BackupSenderEmail->email,'backups');
                }
            }
            EmailLog::AddLog(1,$scene->email_sender_id,$email,$scene->id,$user);
            ReturnJson(true,trans()->get('lang.eamail_success'));
        } catch (\Exception $e) {
            EmailLog::AddLog(1,$scene->email_sender_id,$email,$scene->id,$user);
            ReturnJson(FALSE,$e->getMessage());
        }
    }
    /**
     * reset password eamil send Test
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    public function passwordTest(Request $request){
        $id = $request->user->id;
        $user = User::find($id);
        $user = $user ? $user->toArray() : [];
        $token = $user['email'].'&'.$user['id'];
        $user['token'] = base64_encode($token);
        $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $scene = $request->all();
        $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene['email_sender_id']);
        // 邮箱账号配置信息
        $config = [
            'host' =>  $senderEmail->host,
            'port' =>  $senderEmail->port,
            'encryption' =>  $senderEmail->encryption,
            'username' =>  $senderEmail->email,
            'password' =>  $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email,$scene['body'],$user,$scene['title'],$senderEmail->email);
        return true;
    }


    
    /**
     * 发送订单邮件
     * @param $id 主键ID
     */
    protected function sendOrderEmail(Request $request)
    {

        $record = (new Order())->findOrFail($request->id);

        $code = $record->is_pay == 1?'paid':'order';

        $scene = EmailScene::where('action',$code)->select(['id','name','title','body','email_sender_id','email_recipient','status','alternate_email_id'])->first();
        if(empty($scene)){
            ReturnJson(FALSE,trans()->get('lang.eamail_error'));
        }
        if($scene->status == 0)
        {
            ReturnJson(FALSE,trans()->get('lang.eamail_error'));
        }
        try {
            $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene->email_sender_id);
            // 收件人的数组
            $emails = explode(',',$scene->email_recipient);
            // 再加上订单上的邮箱
            $emails[] = $record->email;
            array_unique($emails);
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
                    if($scene->alternate_email_id){
                        $this->SendEmail($email,$scene->body,[],$scene->title,$BackupSenderEmail->email,'backups');
                    }
                }
            }
            EmailLog::AddLog(1,$scene->email_sender_id,$email,$scene->id,[]);
            ReturnJson(true,trans()->get('lang.eamail_success'));
        } catch (\Exception $e) {
            EmailLog::AddLog(1,$scene->email_sender_id,$email,$scene->id,[]);
            ReturnJson(FALSE,$e->getMessage());
        }
    }
    
    /**
     * reset password eamil send
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    public function resetPassword(Request $request){
        try {
            if(!isset($request->email) || empty($request->email)){
                ReturnJson(FALSE,trans()->get('lang.eamail_empaty'));
            }   
            $email = $request->email;
            $user = User::where('email',$email)->first();
            if(empty($user)){
                ReturnJson(FALSE,trans()->get('lang.eamail_undefined'));
            }
            $user = $user->toArray();
            $token = $user['email'].'&'.$user['id'];
            $user['token'] = base64_encode($token);
            $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
            $scene = EmailScene::where('action','password')->select(['id','name','title','body','email_sender_id','email_recipient','status','alternate_email_id'])->first();
            if(empty($scene)){
                ReturnJson(FALSE,trans()->get('lang.eamail_error'));
            }
            $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene->email_sender_id);
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
            try {
                $this->SendEmail($email,$scene->body,$user,$scene->title,$senderEmail->email);
            } catch (\Exception $e) {
                if($scene->alternate_email_id){
                    $this->SendEmail($email,$scene->body,$user,$scene->title,$BackupSenderEmail->email,'backups');
                }
            }
            EmailLog::AddLog(1,$scene->email_sender_id,$email,$scene->id,$user);
            ReturnJson(true,trans()->get('lang.eamail_success'));
        } catch (\Exception $e) {
            EmailLog::AddLog(1,$scene->email_sender_id,$email,$scene->id,$user);
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * 重置密码的场景的测试请求
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    private function resetPasswordTest($request)
    {
        $id = $request->user->id;
        $user = User::find($id);
        $user = $user ? $user->toArray() : [];
        $token = $user['email'].'&'.$user['id'];
        $user['token'] = base64_encode($token);
        $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $scene = $request->all();
        $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene['email_sender_id']);
        // 收件人的数组
        $emails = explode(',',$scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host' =>  $senderEmail->host,
            'port' =>  $senderEmail->port,
            'encryption' =>  $senderEmail->encryption,
            'username' =>  $senderEmail->email,
            'password' =>  $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email,$scene['body'],$user,$scene['title'],$senderEmail->email);
        return true;
    }

    /**
     * 注册成功的场景的测试请求
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    private function registerSuccessTest($request)
    {
        $id = $request->user->id;
        $user = User::find($id);
        $user = $user ? $user->toArray() : [];
        $token = $user['email'].'&'.$user['id'];
        $user['token'] = base64_encode($token);
        $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $scene = $request->all();
        $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene['email_sender_id']);
        // 收件人的数组
        $emails = explode(',',$scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host' =>  $senderEmail->host,
            'port' =>  $senderEmail->port,
            'encryption' =>  $senderEmail->encryption,
            'username' =>  $senderEmail->email,
            'password' =>  $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email,$scene['body'],$user,$scene['title'],$senderEmail->email);
        return true;
    }

    /**
     * 联系我们的场景的测试请求
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    private function contactUsTest($request)
    {
        $ContactUs = ContactUs::first();
        $data = $ContactUs ? $ContactUs->toArray() : [];
        $data['area_id'] = City::where('id',$data['area_id'])->value('name');
        $token = $data['email'].'&'.$data['id'];
        $data['token'] = base64_encode($token);
        $data['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $siteName = $request->header('Site');
        $siteData = Site::where('name',$siteName)->first();
        $ImageDomain = AliyunOssConfig::where('site_id',$siteData['id'])->value('domain');
        $data2 = [
            'homePage' => $data['domain'],
            'myAccountUrl' => rtrim($data['domain'],'/').'/account/account-infor',
            'contactUsUrl' => rtrim($data['domain'],'/').'/contact-us',
            'homeUrl' => $data['domain'],
            'userName' => $data['name'] ? $data['name'] : '',
            'email' => $data['email'],
            'company' => $data['company'],
            'area' => City::where('id',$data['area_id'])->value('name'),
            'phone' => $data['phone'] ? $data['phone'] : '',
            'plantTimeBuy' => $data['buy_time'],
            'content' => $data['remarks'],
            'backendUrl' => $ImageDomain ? $ImageDomain : '',
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
        ];
        $siteInfo = SystemValue::whereIn('key',['siteName','sitePhone','siteEmail'])->pluck('value','key')->toArray();
        if($siteInfo){
            foreach ($siteInfo as $key => $value) {
                $data[$key] = $value;
            }
        }
        $data = array_merge($data2,$data);
        $scene = $request->all();
        $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene['email_sender_id']);
        // 收件人的数组
        $emails = explode(',',$scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host' =>  $senderEmail->host,
            'port' =>  $senderEmail->port,
            'encryption' =>  $senderEmail->encryption,
            'username' =>  $senderEmail->email,
            'password' =>  $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email,$scene['body'],$data,$scene['title'],$senderEmail->email);
        return true;
    }

    /**
     * 申请样本的场景的测试请求
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    private function productSampleTest($request)
    {
        $ContactUs = ContactUs::first();
        $data = $ContactUs ? $ContactUs->toArray() : [];
        $data['area_id'] = City::where('id',$data['area_id'])->value('name');
        $token = $data['email'].'&'.$data['id'];
        $data['token'] = base64_encode($token);
        $data['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $siteName = $request->header('Site');
        $siteData = Site::where('name',$siteName)->first();
        $ImageDomain = AliyunOssConfig::where('site_id',$siteData['id'])->value('domain');
        $data2 = [
            'homePage' => $data['domain'],
            'myAccountUrl' => rtrim($data['domain'],'/').'/account/account-infor',
            'contactUsUrl' => rtrim($data['domain'],'/').'/contact-us',
            'homeUrl' => $data['domain'],
            'userName' => $data['name'] ? $data['name'] : '',
            'email' => $data['email'],
            'company' => $data['company'],
            'area' => City::where('id',$data['area_id'])->value('name'),
            'phone' => $data['phone'] ? $data['phone'] : '',
            'plantTimeBuy' => $data['buy_time'],
            'content' => $data['remarks'],
            'backendUrl' => $ImageDomain ? $ImageDomain : '',
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
        ];
        $siteInfo = SystemValue::whereIn('key',['siteName','sitePhone','siteEmail'])->pluck('value','key')->toArray();
        if($siteInfo){
            foreach ($siteInfo as $key => $value) {
                $data[$key] = $value;
            }
        }
        $data = array_merge($data2,$data);
        $scene = $request->all();
        $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene['email_sender_id']);
        // 收件人的数组
        $emails = explode(',',$scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host' =>  $senderEmail->host,
            'port' =>  $senderEmail->port,
            'encryption' =>  $senderEmail->encryption,
            'username' =>  $senderEmail->email,
            'password' =>  $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email,$scene['body'],$data,$scene['title'],$senderEmail->email);
        return true;
    }

    /**
     * 申请样本的场景的测试请求
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    private function sampleRequestTest($request)
    {
        $id = $request->user->id;
        $user = User::find($id);
        $user = $user ? $user->toArray() : [];
        $token = $user['email'].'&'.$user['id'];
        $user['token'] = base64_encode($token);
        $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $scene = $request->all();
        $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene['email_sender_id']);
        // 收件人的数组
        $emails = explode(',',$scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host' =>  $senderEmail->host,
            'port' =>  $senderEmail->port,
            'encryption' =>  $senderEmail->encryption,
            'username' =>  $senderEmail->email,
            'password' =>  $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email,$scene['body'],$user,$scene['title'],$senderEmail->email);
        return true;
    }

    /**
     * 定制报告的场景的测试请求
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    private function customizedTest($request)
    {
        $ContactUs = ContactUs::first();
        $data = $ContactUs ? $ContactUs->toArray() : [];
        $data['area_id'] = City::where('id',$data['area_id'])->value('name');
        $token = $data['email'].'&'.$data['id'];
        $data['token'] = base64_encode($token);
        $data['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $siteName = $request->header('Site');
        $siteData = Site::where('name',$siteName)->first();
        $ImageDomain = AliyunOssConfig::where('site_id',$siteData['id'])->value('domain');
        $data2 = [
            'homePage' => $data['domain'],
            'myAccountUrl' => rtrim($data['domain'],'/').'/account/account-infor',
            'contactUsUrl' => rtrim($data['domain'],'/').'/contact-us',
            'homeUrl' => $data['domain'],
            'userName' => $data['name'] ? $data['name'] : '',
            'email' => $data['email'],
            'company' => $data['company'],
            'area' => City::where('id',$data['area_id'])->value('name'),
            'phone' => $data['phone'] ? $data['phone'] : '',
            'plantTimeBuy' => $data['buy_time'],
            'content' => $data['remarks'],
            'backendUrl' => $ImageDomain ? $ImageDomain : '',
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
        ];
        $siteInfo = SystemValue::whereIn('key',['siteName','sitePhone','siteEmail'])->pluck('value','key')->toArray();
        if($siteInfo){
            foreach ($siteInfo as $key => $value) {
                $data[$key] = $value;
            }
        }
        $data = array_merge($data2,$data);
        $scene = $request->all();
        $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene['email_sender_id']);
        // 收件人的数组
        $emails = explode(',',$scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host' =>  $senderEmail->host,
            'port' =>  $senderEmail->port,
            'encryption' =>  $senderEmail->encryption,
            'username' =>  $senderEmail->email,
            'password' =>  $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email,$scene['body'],$data,$scene['title'],$senderEmail->email);
        return true;
    }

    /**
     * 下单付款成功的场景的测试请求
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    private function paymentTest($request)
    {
        $id = $request->user->id;
        $user = User::find($id);
        $user = $user ? $user->toArray() : [];
        $token = $user['email'].'&'.$user['id'];
        $user['token'] = base64_encode($token);
        $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $scene = $request->all();
        $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene['email_sender_id']);
        // 收件人的数组
        $emails = explode(',',$scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host' =>  $senderEmail->host,
            'port' =>  $senderEmail->port,
            'encryption' =>  $senderEmail->encryption,
            'username' =>  $senderEmail->email,
            'password' =>  $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email,$scene['body'],$user,$scene['title'],$senderEmail->email);
        return true;
    }

    /**
     * 下单后未付款的场景的测试请求
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    private function placeOrderTest($request)
    {
        $id = $request->user->id;
        $user = User::find($id);
        $user = $user ? $user->toArray() : [];
        $token = $user['email'].'&'.$user['id'];
        $user['token'] = base64_encode($token);
        $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $scene = $request->all();
        $senderEmail = Email::select(['name','email','host','port','encryption','password'])->find($scene['email_sender_id']);
        // 收件人的数组
        $emails = explode(',',$scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host' =>  $senderEmail->host,
            'port' =>  $senderEmail->port,
            'encryption' =>  $senderEmail->encryption,
            'username' =>  $senderEmail->email,
            'password' =>  $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email,$scene['body'],$user,$scene['title'],$senderEmail->email);
        return true;
    }
}
