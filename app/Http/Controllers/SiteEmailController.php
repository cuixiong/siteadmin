<?php

namespace App\Http\Controllers;

use App\Models\Languages;
use App\Models\PriceEditionValues;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\TrendsEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Modules\Admin\Http\Models\AliyunOssConfig;
use Modules\Admin\Http\Models\City;
use Modules\Admin\Http\Models\Language;
use Modules\Admin\Http\Models\PriceEditionValue;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Controllers\OperationLogController;
use Modules\Site\Http\Models\ContactUs;
use Modules\Site\Http\Models\Email;
use Modules\Site\Http\Models\EmailLog;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\User;
use Modules\Site\Http\Models\EmailScene;
use Modules\Site\Http\Models\OrderGoods;
use Modules\Site\Http\Models\Pay;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\SystemValue;

class SiteEmailController extends Controller {
    // 注册发邮method
    private $EmailCodes = ['register' => '注册账号'];
    public  $signKey    = '62d9048a8a2ee148cf142a0e6696ab26';

    /**
     * 动态配置邮箱参数
     *
     * @param array $data 邮箱配置参数信息
     */
    private function SetConfig($data, $name = 'trends') {
        $keys = ['transport', 'host', 'port', 'encryption', 'username', 'password', 'timeout', 'local_domain'];
        foreach ($data as $key => $value) {
            if (in_array($key, $keys)) {
                Config::set('mail.mailers.'.$name.'.'.$key, $value, true);
            }
        }

        return true;
    }

    /**
     * 发送邮箱
     *
     * @param string $email     接收邮箱号
     * @param string $templet   邮箱字符串的模板
     * @param array  $data      渲染模板需要的数据
     * @param string $subject   邮箱标题
     * @param string $EmailUser 邮箱发件人
     */
    private function SendEmail($email, $templet, $data, $subject, $EmailUser, $name = 'trends') {
        $res = Mail::mailer($name)->to($email)->send(new TrendsEmail($templet, $data, $subject, $EmailUser));

        return $res;
    }

    /**
     * 邮件测试发送中转方法
     *
     * @param use Illuminate\Http\Request;
     */
    public function test(Request $request) {
        try {
            //验证表单数据
            $this->validatorData($request->all());
            $action = $request->action;
            if (in_array($action, ['register', 'registerSuccess'])) {
//                $actionMethod = $request->action.'Test';
//                $res = $this->$actionMethod($request);
                $user = User::where('email', $request->test)->first();
                if(empty($user )){
                    ReturnJson(false, '邮箱用户不存在');
                }
                $testEmail = $user->id;
                $res = $this->sendTestEmail($action, $testEmail);
            } else {
                $testEmail = $request->test;
                $res = $this->sendTestEmail($action, $testEmail);
            }
            $res
                ? ReturnJson(true, trans()->get('lang.eamail_success'))
                : ReturnJson(
                false, trans()->get('lang.eamail_error')
            );
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function sendTestEmail($action, $email) {
        $domain = getSiteDomain();
        $url = $domain.'/api/third/test-send-email';
        $reqData = [
            'action'    => $action,
            'testEmail' => $email,
        ];
        $reqData['sign'] = $this->makeSign($reqData, $this->signKey);
        $response = Http::post($url, $reqData);
        $resp = $response->json();
        if (!empty($resp) && $resp['code'] == 200) {
            ReturnJson(true, '发送成功');
        } else {
            \Log::error('返回结果数据:'.json_encode($resp));
            ReturnJson(false, '发送失败,未知错误');
        }
    }

    /**
     * 邮箱测试发送表单数据验证
     *
     * @param array $data ;
     */
    private function validatorData($data) {
        $rules = [
            'name'            => 'required',
            'title'           => 'required',
            'body'            => 'required',
            'email_sender_id' => 'required',
            //'email_recipient' => 'required',
            'action'          => 'required',
        ];
        $message = [
            'name.required'            => '场景名称不能为空',
            'title.required'           => '邮箱标题不能为空',
            'body.required'            => '邮箱内容不能为空',
            'email_sender_id.required' => '发送邮件的邮箱ID不能为空',
            //'email_recipient.required' => '邮箱收件人不能为空',
            'action.required'          => '测试邮箱的code方法不能为空',
            'test.required'            => '测试邮箱不能为空',
        ];
        $validator = Validator::make($data, $rules, $message);
        if ($validator->fails()) {
            $errors = $validator->errors()->messages();
            $errors = array_shift($errors['action']);
            ReturnJson(false, $errors);
        }
        //校验test字段必须是邮箱
        $testEmail = $data['test'] ?? '';
        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            ReturnJson(false, '测试邮箱格式不正确');
        }
    }

    /**
     * return email method
     */
    public function EmailCode() {
        $list = [];
        if (empty($this->EmailCodes)) {
            ReturnJson(true, '', $list);
        }
        foreach ($this->EmailCodes as $key => $value) {
            $list[] = [
                'value' => $key,
                'label' => "$value($key)"
            ];
        }
        ReturnJson(true, '', $list);
    }

    /**
     * 注册场景的发邮请求
     *
     * @param use Illuminate\Http\Request;
     *
     * @return response Code
     */
    public function register($id) {
        try {
            $user = User::find($id);
            $user = $user ? $user->toArray() : [];
            $token = $user['email'].'&'.$user['id'];
            $user['token'] = base64_encode($token);
            $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
            $scene = EmailScene::where('action', 'register')->select(
                ['id', 'name', 'title', 'body', 'email_sender_id', 'email_recipient', 'status', 'alternate_email_id']
            )->first();
            if (empty($scene)) {
                ReturnJson(false, trans()->get('lang.eamail_error'));
            }
            if ($scene->status == 0) {
                ReturnJson(false, trans()->get('lang.eamail_error'));
            }
            $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
                $scene->email_sender_id
            );
            // 收件人的数组
            $emails = explode(',', $scene->email_recipient);
            // 邮箱账号配置信息
            $config = [
                'host'       => $senderEmail->host,
                'port'       => $senderEmail->port,
                'encryption' => $senderEmail->encryption,
                'username'   => $senderEmail->email,
                'password'   => $senderEmail->password
            ];
            $this->SetConfig($config);
            if ($scene->alternate_email_id) {
                // 备用邮箱配置信息
                $BackupSenderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
                    $scene->alternate_email_id
                );
                $BackupConfig = [
                    'host'       => $BackupSenderEmail->host,
                    'port'       => $BackupSenderEmail->port,
                    'encryption' => $BackupSenderEmail->encryption,
                    'username'   => $BackupSenderEmail->email,
                    'password'   => $BackupSenderEmail->password
                ];
                $this->SetConfig($BackupConfig, 'backups');// 若发送失败，则使用备用邮箱发送
            }
            foreach ($emails as $email) {
                try {
                    $this->SendEmail($email, $scene->body, $user, $scene->title, $senderEmail->email);
                } catch (\Exception $e) {
                    if ($scene->alternate_email_id) {
                        $this->SendEmail(
                            $email, $scene->body, $user, $scene->title, $BackupSenderEmail->email, 'backups'
                        );
                    }
                }
            }
            EmailLog::AddLog(1, $scene->email_sender_id, $emails, $scene->id, $user);
            ReturnJson(true, trans()->get('lang.eamail_success'));
        } catch (\Exception $e) {
            EmailLog::AddLog(0, $scene->email_sender_id, $emails, $scene->id, $user);
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 注册场景的测试请求
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    private function registerTest($request) {
        $user = User::where('email', $request->test)->first();
        if (!$user) {
            $user = User::first();
        }
        $data = $user ? $user->toArray() : [];
        $siteName = $request->header('Site');
        $siteData = Site::where('name', $siteName)->first();
        $data['domain'] = 'https://'.$siteData['domain'];
        $ImageDomain = AliyunOssConfig::where('site_id', $siteData['id'])->value('domain');
        $token = $data['email'].'&'.$data['id'];
        $data['token'] = base64_encode($token);
        $emailCode = 'signupToBeMember';
        $dataQuery = [
            'timestamp' => time(),
            'randomstr' => '123',
            'authkey'   => '123',
            'sign'      => $data['token'],
        ];
        $verifyUrl = $data['domain'].'/?verifyemail='.$emailCode.'&'.http_build_query($dataQuery);
        $data2 = [
            'homePage'     => $data['domain'],
            'myAccountUrl' => rtrim($data['domain'], '/').'/account/account-infor',
            'contactUsUrl' => rtrim($data['domain'], '/').'/contact-us',
            'homeUrl'      => $data['domain'],
            'backendUrl'   => $ImageDomain ? $ImageDomain : '',
            'verifyUrl'    => $verifyUrl,
            'userName'     => $data['name'],
            'area'         => City::where('id', $data['area_id'])->value('name'),
        ];
        $siteInfo = SystemValue::whereIn('key', ['siteName', 'sitePhone', 'siteEmail'])->pluck('value', 'key')->toArray(
        );
        if ($siteInfo) {
            foreach ($siteInfo as $key => $value) {
                $data[$key] = $value;
            }
        }
        $data = array_merge($data2, $data);
        $scene = $request->all();
        $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
            $scene['email_sender_id']
        );
        // 收件人的数组
        $emails = explode(',', $scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host'       => $senderEmail->host,
            'port'       => $senderEmail->port,
            'encryption' => $senderEmail->encryption,
            'username'   => $senderEmail->email,
            'password'   => $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email, $scene['body'], $data, $scene['title'], $senderEmail->email);

        return true;
    }

    /**
     * reset password eamil send
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    public function password(Request $request) {
        try {
            if (!isset($request->email) || empty($request->email)) {
                ReturnJson(false, trans()->get('lang.eamail_empaty'));
            }
            $email = $request->email;
            $user = User::where('email', $email)->first();
            if (empty($user)) {
                ReturnJson(false, trans()->get('lang.eamail_undefined'));
            }
            $user = $user->toArray();
            $token = $user['email'].'&'.$user['id'];
            $user['token'] = base64_encode($token);
            $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
            $scene = EmailScene::where('action', 'password')->select(
                ['id', 'name', 'title', 'body', 'email_sender_id', 'email_recipient', 'status', 'alternate_email_id']
            )->first();
            if (empty($scene)) {
                ReturnJson(false, trans()->get('lang.eamail_error'));
            }
            $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
                $scene->email_sender_id
            );
            // 邮箱账号配置信息
            $config = [
                'host'       => $senderEmail->host,
                'port'       => $senderEmail->port,
                'encryption' => $senderEmail->encryption,
                'username'   => $senderEmail->email,
                'password'   => $senderEmail->password
            ];
            $this->SetConfig($config);
            if ($scene->alternate_email_id) {
                // 备用邮箱配置信息
                $BackupSenderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
                    $scene->alternate_email_id
                );
                $BackupConfig = [
                    'host'       => $BackupSenderEmail->host,
                    'port'       => $BackupSenderEmail->port,
                    'encryption' => $BackupSenderEmail->encryption,
                    'username'   => $BackupSenderEmail->email,
                    'password'   => $BackupSenderEmail->password
                ];
                $this->SetConfig($BackupConfig, 'backups');// 若发送失败，则使用备用邮箱发送
            }
            try {
                $this->SendEmail($email, $scene->body, $user, $scene->title, $senderEmail->email);
            } catch (\Exception $e) {
                if ($scene->alternate_email_id) {
                    $this->SendEmail($email, $scene->body, $user, $scene->title, $BackupSenderEmail->email, 'backups');
                }
            }
            EmailLog::AddLog(1, $scene->email_sender_id, $email, $scene->id, $user);
            ReturnJson(true, trans()->get('lang.eamail_success'));
        } catch (\Exception $e) {
            EmailLog::AddLog(1, $scene->email_sender_id, $email, $scene->id, $user);
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * reset password eamil send Test
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    public function passwordTest(Request $request) {
        $id = $request->user->id;
        $user = User::find($id);
        $user = $user ? $user->toArray() : [];
        $token = $user['email'].'&'.$user['id'];
        $user['token'] = base64_encode($token);
        $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $scene = $request->all();
        $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
            $scene['email_sender_id']
        );
        // 邮箱账号配置信息
        $config = [
            'host'       => $senderEmail->host,
            'port'       => $senderEmail->port,
            'encryption' => $senderEmail->encryption,
            'username'   => $senderEmail->email,
            'password'   => $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email, $scene['body'], $user, $scene['title'], $senderEmail->email);

        return true;
    }

    /**
     * 发送订单邮件
     *
     * @param $id 主键ID
     */
    protected function sendOrderEmail(Request $request) {
        try {
            $ids = [];
            if (!empty($request->ids)) {
                if (is_array($request->ids)) {
                    $ids = $request->ids;
                } else {
                    $ids = [$request->ids];
                }
            } elseif (!empty($request->id)) {
                $ids = [$request->id];
            }
            if (empty($ids)) {
                ReturnJson(false, '订单ID不能为空');
            }
            $site = $request->header('site');
            $domain = Site::where('name', $site)->value("domain");
            if (empty($domain)) {
                ReturnJson(false, '站点配置异常');
            }
            if (strpos($domain, '://') === false) {
                $domain = 'https://'.$domain;
            }
            $url = $domain.'/api/third/send-email';
            $sucCnt = 0;
            $errMsg = [];
            foreach ($ids as $id) {
                $record = (new Order())->findOrFail($id);
                //已支付与已完成
                if (in_array($record->is_pay, [2, 4])) {
                    $code = 'paySuccess';
                } else {
                    $code = 'placeOrder';
                }
                $reqData = [
                    'id'   => $id,
                    'code' => $code,
                ];
                $reqData['sign'] = $this->makeSign($reqData, $this->signKey);
                //\Log::error('返回结果数据:'.json_encode([$url, $reqData]).'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
                $response = Http::post($url, $reqData);
                $resp = $response->json();
                if (!empty($resp) && $resp['code'] == 200) {
                    $sucCnt++;
                    //添加日志：复用与 SiteOperationLog 一致的通道，type 使用 'email'
                    OperationLogController::AddLog($record, 'email');
                } else {
                    $errMsg[] = $resp;
                }
            }
            if (empty($errMsg)) {
                ReturnJson(true, "发送成功:{$sucCnt}次");
            } else {
                \Log::error('返回结果数据:'.json_encode($errMsg));
                ReturnJson(false, '发送失败,未知错误');
            }
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * reset password eamil send
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    public function resetPassword(Request $request) {
        try {
            if (!isset($request->email) || empty($request->email)) {
                ReturnJson(false, trans()->get('lang.eamail_empaty'));
            }
            $email = $request->email;
            $user = User::where('email', $email)->first();
            if (empty($user)) {
                ReturnJson(false, trans()->get('lang.eamail_undefined'));
            }
            $user = $user->toArray();
            $token = $user['email'].'&'.$user['id'];
            $user['token'] = base64_encode($token);
            $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
            $scene = EmailScene::where('action', 'password')->select(
                ['id', 'name', 'title', 'body', 'email_sender_id', 'email_recipient', 'status', 'alternate_email_id']
            )->first();
            if (empty($scene)) {
                ReturnJson(false, trans()->get('lang.eamail_error'));
            }
            $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
                $scene->email_sender_id
            );
            // 邮箱账号配置信息
            $config = [
                'host'       => $senderEmail->host,
                'port'       => $senderEmail->port,
                'encryption' => $senderEmail->encryption,
                'username'   => $senderEmail->email,
                'password'   => $senderEmail->password
            ];
            $this->SetConfig($config);
            if ($scene->alternate_email_id) {
                // 备用邮箱配置信息
                $BackupSenderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
                    $scene->alternate_email_id
                );
                $BackupConfig = [
                    'host'       => $BackupSenderEmail->host,
                    'port'       => $BackupSenderEmail->port,
                    'encryption' => $BackupSenderEmail->encryption,
                    'username'   => $BackupSenderEmail->email,
                    'password'   => $BackupSenderEmail->password
                ];
                $this->SetConfig($BackupConfig, 'backups');// 若发送失败，则使用备用邮箱发送
            }
            try {
                $this->SendEmail($email, $scene->body, $user, $scene->title, $senderEmail->email);
            } catch (\Exception $e) {
                if ($scene->alternate_email_id) {
                    $this->SendEmail($email, $scene->body, $user, $scene->title, $BackupSenderEmail->email, 'backups');
                }
            }
            EmailLog::AddLog(1, $scene->email_sender_id, $email, $scene->id, $user);
            ReturnJson(true, trans()->get('lang.eamail_success'));
        } catch (\Exception $e) {
            EmailLog::AddLog(1, $scene->email_sender_id, $email, $scene->id, $user);
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 重置密码的场景的测试请求
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    private function resetPasswordTest($request) {
        $id = $request->user->id;
        $user = User::find($id);
        $user = $user ? $user->toArray() : [];
        $token = $user['email'].'&'.$user['id'];
        $user['token'] = base64_encode($token);
        $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $scene = $request->all();
        $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
            $scene['email_sender_id']
        );
        // 收件人的数组
        $emails = explode(',', $scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host'       => $senderEmail->host,
            'port'       => $senderEmail->port,
            'encryption' => $senderEmail->encryption,
            'username'   => $senderEmail->email,
            'password'   => $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email, $scene['body'], $user, $scene['title'], $senderEmail->email);

        return true;
    }

    /**
     * 注册成功的场景的测试请求
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    private function registerSuccessTest($request) {
        $user = User::where('email', $request->test)->first();
        if (!$user) {
            $user = User::first();
        }
        $data = $user ? $user->toArray() : [];
        $siteName = $request->header('Site');
        $siteData = Site::where('name', $siteName)->first();
        $data['domain'] = 'https://'.$siteData['domain'];
        $ImageDomain = AliyunOssConfig::where('site_id', $siteData['id'])->value('domain');
        $token = $data['email'].'&'.$data['id'];
        $data['token'] = base64_encode($token);
        $data2 = [
            'homePage'     => $data['domain'],
            'myAccountUrl' => rtrim($data['domain'], '/').'/account/account-infor',
            'contactUsUrl' => rtrim($data['domain'], '/').'/contact-us',
            'homeUrl'      => $data['domain'],
            'backendUrl'   => $ImageDomain ? $ImageDomain : '',
            'verifyUrl'    => '',
            'userName'     => $data['name'],
            'area'         => City::where('id', $data['area_id'])->value('name'),
        ];
        $siteInfo = SystemValue::whereIn('key', ['siteName', 'sitePhone', 'siteEmail'])->pluck('value', 'key')->toArray(
        );
        if ($siteInfo) {
            foreach ($siteInfo as $key => $value) {
                $data[$key] = $value;
            }
        }
        $data = array_merge($data2, $data);
        $scene = $request->all();
        $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
            $scene['email_sender_id']
        );
        // 收件人的数组
        $emails = explode(',', $scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host'       => $senderEmail->host,
            'port'       => $senderEmail->port,
            'encryption' => $senderEmail->encryption,
            'username'   => $senderEmail->email,
            'password'   => $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email, $scene['body'], $data, $scene['title'], $senderEmail->email);

        return true;
    }

    /**
     * 联系我们的场景的测试请求
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    private function contactUsTest($request) {
        $ContactUs = ContactUs::first();
        $data = $ContactUs ? $ContactUs->toArray() : [];
        $data['area_id'] = City::where('id', $data['area_id'])->value('name');
        $token = $data['email'].'&'.$data['id'];
        $data['token'] = base64_encode($token);
        $data['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $siteName = $request->header('Site');
        $siteData = Site::where('name', $siteName)->first();
        $ImageDomain = AliyunOssConfig::where('site_id', $siteData['id'])->value('domain');
        $data2 = [
            'homePage'     => $data['domain'],
            'myAccountUrl' => rtrim($data['domain'], '/').'/account/account-infor',
            'contactUsUrl' => rtrim($data['domain'], '/').'/contact-us',
            'homeUrl'      => $data['domain'],
            'userName'     => $data['name'] ? $data['name'] : '',
            'email'        => $data['email'],
            'company'      => $data['company'],
            'area'         => City::where('id', $data['area_id'])->value('name'),
            'phone'        => $data['phone'] ? $data['phone'] : '',
            'plantTimeBuy' => $data['buy_time'],
            'content'      => $data['remarks'],
            'backendUrl'   => $ImageDomain ? $ImageDomain : '',
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
        ];
        $siteInfo = SystemValue::whereIn('key', ['siteName', 'sitePhone', 'siteEmail'])->pluck('value', 'key')->toArray(
        );
        if ($siteInfo) {
            foreach ($siteInfo as $key => $value) {
                $data[$key] = $value;
            }
        }
        $data = array_merge($data2, $data);
        $scene = $request->all();
        $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
            $scene['email_sender_id']
        );
        // 收件人的数组
        $emails = explode(',', $scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host'       => $senderEmail->host,
            'port'       => $senderEmail->port,
            'encryption' => $senderEmail->encryption,
            'username'   => $senderEmail->email,
            'password'   => $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email, $scene['body'], $data, $scene['title'], $senderEmail->email);

        return true;
    }

    /**
     * 申请样本的场景的测试请求
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    private function productSampleTest($request) {
        $ContactUs = ContactUs::first();
        $data = $ContactUs ? $ContactUs->toArray() : [];
        $data['area_id'] = City::where('id', $data['area_id'])->value('name');
        $token = $data['email'].'&'.$data['id'];
        $data['token'] = base64_encode($token);
        $data['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $siteName = $request->header('Site');
        $siteData = Site::where('name', $siteName)->first();
        $ImageDomain = AliyunOssConfig::where('site_id', $siteData['id'])->value('domain');
        $data2 = [
            'homePage'     => $data['domain'],
            'myAccountUrl' => rtrim($data['domain'], '/').'/account/account-infor',
            'contactUsUrl' => rtrim($data['domain'], '/').'/contact-us',
            'homeUrl'      => $data['domain'],
            'userName'     => $data['name'] ? $data['name'] : '',
            'email'        => $data['email'],
            'company'      => $data['company'],
            'area'         => City::where('id', $data['area_id'])->value('name'),
            'phone'        => $data['phone'] ? $data['phone'] : '',
            'plantTimeBuy' => $data['buy_time'],
            'content'      => $data['remarks'],
            'backendUrl'   => $ImageDomain ? $ImageDomain : '',
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
        ];
        $siteInfo = SystemValue::whereIn('key', ['siteName', 'sitePhone', 'siteEmail'])->pluck('value', 'key')->toArray(
        );
        if ($siteInfo) {
            foreach ($siteInfo as $key => $value) {
                $data[$key] = $value;
            }
        }
        $data = array_merge($data2, $data);
        $scene = $request->all();
        $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
            $scene['email_sender_id']
        );
        // 收件人的数组
        $emails = explode(',', $scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host'       => $senderEmail->host,
            'port'       => $senderEmail->port,
            'encryption' => $senderEmail->encryption,
            'username'   => $senderEmail->email,
            'password'   => $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email, $scene['body'], $data, $scene['title'], $senderEmail->email);

        return true;
    }

    /**
     * 申请样本的场景的测试请求
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    private function sampleRequestTest($request) {
        $id = $request->user->id;
        $user = User::find($id);
        $user = $user ? $user->toArray() : [];
        $token = $user['email'].'&'.$user['id'];
        $user['token'] = base64_encode($token);
        $user['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $scene = $request->all();
        $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
            $scene['email_sender_id']
        );
        // 收件人的数组
        $emails = explode(',', $scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host'       => $senderEmail->host,
            'port'       => $senderEmail->port,
            'encryption' => $senderEmail->encryption,
            'username'   => $senderEmail->email,
            'password'   => $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email, $scene['body'], $user, $scene['title'], $senderEmail->email);

        return true;
    }

    /**
     * 定制报告的场景的测试请求
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    private function customizedTest($request) {
        $ContactUs = ContactUs::first();
        $data = $ContactUs ? $ContactUs->toArray() : [];
        $data['area_id'] = City::where('id', $data['area_id'])->value('name');
        $token = $data['email'].'&'.$data['id'];
        $data['token'] = base64_encode($token);
        $data['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $siteName = $request->header('Site');
        $siteData = Site::where('name', $siteName)->first();
        $ImageDomain = AliyunOssConfig::where('site_id', $siteData['id'])->value('domain');
        $data2 = [
            'homePage'     => $data['domain'],
            'myAccountUrl' => rtrim($data['domain'], '/').'/account/account-infor',
            'contactUsUrl' => rtrim($data['domain'], '/').'/contact-us',
            'homeUrl'      => $data['domain'],
            'userName'     => $data['name'] ? $data['name'] : '',
            'email'        => $data['email'],
            'company'      => $data['company'],
            'area'         => City::where('id', $data['area_id'])->value('name'),
            'phone'        => $data['phone'] ? $data['phone'] : '',
            'plantTimeBuy' => $data['buy_time'],
            'content'      => $data['remarks'],
            'backendUrl'   => $ImageDomain ? $ImageDomain : '',
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
            'plantTimeBuy' => $data['buy_time'],
        ];
        $siteInfo = SystemValue::whereIn('key', ['siteName', 'sitePhone', 'siteEmail'])->pluck('value', 'key')->toArray(
        );
        if ($siteInfo) {
            foreach ($siteInfo as $key => $value) {
                $data[$key] = $value;
            }
        }
        $data = array_merge($data2, $data);
        $scene = $request->all();
        $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
            $scene['email_sender_id']
        );
        // 收件人的数组
        $emails = explode(',', $scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host'       => $senderEmail->host,
            'port'       => $senderEmail->port,
            'encryption' => $senderEmail->encryption,
            'username'   => $senderEmail->email,
            'password'   => $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email, $scene['body'], $data, $scene['title'], $senderEmail->email);

        return true;
    }

    /**
     * 下单付款成功的场景的测试请求
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    private function paymentTest($request) {
        $user = User::where('email', $request->test)->first();
        if ($user) {
            $Order = Order::where('user_id', $user->id)->where('is_pay', 0)->first();
            if (!$Order) {
                $Order = Order::where('is_pay', 1)->first();
            }
        } else {
            $Order = Order::where('is_pay', 1)->first();
        }
        $data = $Order ? $Order->toArray() : [];
        if (!$data) {
            ReturnJson(false, '未找到订单数据');
        }
        $user = User::find($data['user_id']);
        $user = $user ? $user->toArray() : [];
        $data['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $siteName = $request->header('Site');
        $siteData = Site::where('name', $siteName)->first();
        $ImageDomain = AliyunOssConfig::where('site_id', $siteData['id'])->value('domain');
        $PayName = Pay::where('id', $data['pay_type'])->value('name');
        $OrderGoods = OrderGoods::where('order_id', $data['id'])->first();
        $priceEdition = PriceEditionValue::where('id', $OrderGoods['price_edition'])->first();
        $language = Language::where('id', $priceEdition['language_id'])->value('name');
        $Products = Products::select(
            ['url as link', 'thumb', 'name', 'id as product_id', 'published_date', 'category_id']
        )->whereIn('id', explode(',', $OrderGoods['goods_id']))->get()->toArray();
        if ($Products) {
            foreach ($Products as $key => $value) {
                $Products[$key]['goods_number'] = $OrderGoods['goods_number'] ? intval($OrderGoods['goods_number']) : 0;
                $Products[$key]['language'] = $language;
                $Products[$key]['price_edition'] = $priceEdition['name'];
                $Products[$key]['goods_present_price'] = $OrderGoods['goods_present_price'];
                if (empty($value['thumb'])) {
                    $categoryThumb = ProductsCategory::where('id', $value['category_id'])->value('thumb');
                    $Products[$key]['thumb'] = rtrim($ImageDomain, '/').$categoryThumb;
                } else {
                    $Products[$key]['thumb'] = rtrim($ImageDomain, '/').$value['thumb'];
                }
            }
        }
        $cityName = City::where('id', $data['city_id'])->value('name');
        $provinceName = City::where('id', $data['province_id'])->value('name');
        $addres = $provinceName.' '.$cityName.' '.$data['address'];
        $data2 = [
            'homePage'           => $data['domain'],
            'myAccountUrl'       => rtrim($data['domain'], '/').'/account/account-infor',
            'contactUsUrl'       => rtrim($data['domain'], '/').'/contact-us',
            'homeUrl'            => $data['domain'],
            'backendUrl'         => $ImageDomain ? $ImageDomain : '',
            'userName'           => $data['username'] ? $data['username'] : '',
            'userEmail'          => $data['email'],
            'userCompany'        => $data['company'],
            'userAddress'        => $addres,
            'userPhone'          => $data['phone'] ? $data['phone'] : '',
            'orderStatus'        => '已付款',
            'paymentMethod'      => $PayName,
            'orderAmount'        => $data['order_amount'],
            'preferentialAmount' => $data['order_amount'] - $data['actually_paid'],
            'orderActuallyPaid'  => $data['actually_paid'],
            'orderNumber'        => $data['order_number'],
            'paymentLink'        => $siteData['domain'].'/api/order/pay?order_id='.$data['id'],
            'orderDetails'       => $siteData['domain'].'/account?orderdetails='.$data['id'],
            'goods'              => $Products,
            'userId'             => $user['id']
        ];
        $siteInfo = SystemValue::whereIn('key', ['siteName', 'sitePhone', 'siteEmail'])->pluck('value', 'key')->toArray(
        );
        if ($siteInfo) {
            foreach ($siteInfo as $key => $value) {
                $data[$key] = $value;
            }
        }
        $data = array_merge($data2, $data);
        $scene = $request->all();
        $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
            $scene['email_sender_id']
        );
        // 收件人的数组
        $emails = explode(',', $scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host'       => $senderEmail->host,
            'port'       => $senderEmail->port,
            'encryption' => $senderEmail->encryption,
            'username'   => $senderEmail->email,
            'password'   => $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email, $scene['body'], $data, $scene['title'], $senderEmail->email);

        return true;
    }

    /**
     * 下单后未付款的场景的测试请求
     *
     * @param use Illuminate\Http\Request $request;
     *
     * @return response Code
     */
    private function placeOrderTest($request) {
        $user = User::where('email', $request->test)->first();
        if ($user) {
            $Order = Order::where('user_id', $user->id)->where('is_pay', 0)->first();
            if (!$Order) {
                $Order = Order::where('is_pay', 1)->first();
            }
        } else {
            $Order = Order::where('is_pay', 1)->first();
        }
        $data = $Order ? $Order->toArray() : [];
        if (!$data) {
            ReturnJson(false, '未找到订单数据');
        }
        $user = User::find($data['user_id']);
        $user = $user ? $user->toArray() : [];
        $data['domain'] = 'http://'.$_SERVER['SERVER_NAME'];
        $siteName = $request->header('Site');
        $siteData = Site::where('name', $siteName)->first();
        $ImageDomain = AliyunOssConfig::where('site_id', $siteData['id'])->value('domain');
        $PayName = Pay::where('id', $data['pay_type'])->value('name');
        $OrderGoods = OrderGoods::where('order_id', $data['id'])->first();
        $priceEdition = PriceEditionValue::where('id', $OrderGoods['price_edition'])->first();
        $language = Language::where('id', $priceEdition['language_id'])->value('name');
        $Products = Products::select(
            ['url as link', 'thumb', 'name', 'id as product_id', 'published_date', 'category_id']
        )->whereIn('id', explode(',', $OrderGoods['goods_id']))->get()->toArray();
        if ($Products) {
            foreach ($Products as $key => $value) {
                $Products[$key]['goods_number'] = $OrderGoods['goods_number'] ? intval($OrderGoods['goods_number']) : 0;
                $Products[$key]['language'] = $language;
                $Products[$key]['price_edition'] = $priceEdition['name'];
                $Products[$key]['goods_present_price'] = $OrderGoods['goods_present_price'];
                if (empty($value['thumb'])) {
                    $categoryThumb = ProductsCategory::where('id', $value['category_id'])->value('thumb');
                    $Products[$key]['thumb'] = rtrim($ImageDomain, '/').$categoryThumb;
                } else {
                    $Products[$key]['thumb'] = rtrim($ImageDomain, '/').$value['thumb'];
                }
            }
        }
        $cityName = City::where('id', $data['city_id'])->value('name');
        $provinceName = City::where('id', $data['province_id'])->value('name');
        $addres = $provinceName.' '.$cityName.' '.$data['address'];
        $data2 = [
            'homePage'           => $data['domain'],
            'myAccountUrl'       => rtrim($data['domain'], '/').'/account/account-infor',
            'contactUsUrl'       => rtrim($data['domain'], '/').'/contact-us',
            'homeUrl'            => $data['domain'],
            'backendUrl'         => $ImageDomain ? $ImageDomain : '',
            'userName'           => $data['username'] ? $data['username'] : '',
            'userEmail'          => $data['email'],
            'userCompany'        => $data['company'],
            'userAddress'        => $addres,
            'userPhone'          => $data['phone'] ? $data['phone'] : '',
            'orderStatus'        => '未付款',
            'paymentMethod'      => $PayName,
            'orderAmount'        => $data['order_amount'],
            'preferentialAmount' => $data['order_amount'] - $data['actually_paid'],
            'orderActuallyPaid'  => $data['actually_paid'],
            'orderNumber'        => $data['order_number'],
            'paymentLink'        => $siteData['domain'].'/api/order/pay?order_id='.$data['id'],
            'orderDetails'       => $siteData['domain'].'/account?orderdetails='.$data['id'],
            'goods'              => $Products,
            'userId'             => $user['id']
        ];
        $siteInfo = SystemValue::whereIn('key', ['siteName', 'sitePhone', 'siteEmail'])->pluck('value', 'key')->toArray(
        );
        if ($siteInfo) {
            foreach ($siteInfo as $key => $value) {
                $data[$key] = $value;
            }
        }
        $data = array_merge($data2, $data);
        $scene = $request->all();
        $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
            $scene['email_sender_id']
        );
        // 收件人的数组
        $emails = explode(',', $scene['email_recipient']);
        // 邮箱账号配置信息
        $config = [
            'host'       => $senderEmail->host,
            'port'       => $senderEmail->port,
            'encryption' => $senderEmail->encryption,
            'username'   => $senderEmail->email,
            'password'   => $senderEmail->password
        ];
        $this->SetConfig($config);
        $email = $request->test ? $request->test : $request->user->email;
        $this->SendEmail($email, $scene['body'], $data, $scene['title'], $senderEmail->email);

        return true;
    }

    public function makeSign($data, $signkey) {
        unset($data['sign']);
        $signStr = '';
        ksort($data);
        foreach ($data as $key => $value) {
            $signStr .= $key.'='.$value.'&';
        }
        $signStr .= "key={$signkey}";

        //dump($signStr);
        return md5($signStr);
    }
}
