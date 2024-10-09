<?php

namespace Modules\Site\Http\Controllers;

use App\Models\SiteUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Http\Models\City;
use Modules\Admin\Http\Models\Country;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\UserAddress;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends CrudController {
    public function options(Request $request) {
        $options = [];
        $codes = ['Switch_State'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code', $codes)->where('status', 1)->select('code', 'value', $NameField)
                               ->orderBy('sort', 'asc')->get()->toArray();
        if (!empty($data)) {
            foreach ($data as $map) {
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        $options['country'] = Country::where('status', 1)->select('id as value', 'name as label')->orderBy(
            'sort', 'asc'
        )->get()->toArray();
        $provinces = City::where(['status' => 1, 'type' => 1])->select('id as value', 'name as label')->orderBy(
            'id', 'asc'
        )->get()->toArray();
        foreach ($provinces as $key => $province) {
            $cities = City::where(['status' => 1, 'type' => 2, 'pid' => $province['value']])->select(
                'id as value', 'name as label'
            )->orderBy('id', 'asc')->get()->toArray();
            $provinces[$key]['children'] = $cities;
        }
        $options['city'] = $provinces;
        ReturnJson(true, '', $options);
    }

    /**
     * AJax单个查询
     *
     * @param $request 请求信息
     */
    protected function form(Request $request) {
        try {
            $this->ValidateInstance($request);
            $data = [];
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!empty($record)) {
                $record = $record->toArray();
                $record = Arr::except($record, ['password', 'token']);
            }
            $data['user'] = $record;
            //获取用户收货地址
            $user_address_list = (new UserAddress())->where("user_id", $record['id'])->get()->toArray();
            $data['user_address_list'] = $user_address_list;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 单个新增
     *
     * @param $request 请求信息
     */
    protected function store(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            //邮箱唯一校验
            $user_email_count = SiteUser::query()->where('email', $request->email)->count();
            if ($user_email_count > 0) {
                ReturnJson(false, trans('lang.email_unique'));
            }
            $input['check_email'] = 1; //默认已验证
            $input['status'] = 1; //默认已验证
            $password = request()->input('password', '123456');
            $input['password'] = Hash::make($password);
            $record = $this->ModelInstance()->create($input);
            if (!$record) {
                ReturnJson(false, trans('lang.add_error'));
            }
            ReturnJson(true, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * AJax单个更新
     *
     * @param $request 请求信息
     */
    public function update(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $userAddessList = [];
            if (!empty($input['userAddess'])) {
                $userAddessList = @json_decode($input['userAddess'], true);
            }
            $siteUserModel = new SiteUser();
            $user = $siteUserModel->findOrFail($request->id);
            //邮箱唯一校验
            $user_email_count = $siteUserModel->where('email', $request->email)->where('id', '<>', $request->id)->count(
            );
            if ($user_email_count > 0) {
                ReturnJson(false, trans('lang.email_unique'));
            }
            $userData = [];
            $userData['id'] = $request->id;
            $userData['username'] = $request->username;
            $userData['email'] = $request->email;
            $userData['phone'] = $request->phone;
            $userData['area_id'] = $request->area_id;
            $userData['status'] = $request->status;
            $userData['company'] = $request->company;
            $userData['address'] = $request->address;
            $userData['province_id'] = $request->province_id;
            $userData['city_id'] = $request->city_id;
            if (!empty($input['password'])) {
                //管理员重置密码
                $userData['password'] = Hash::make($input['password']);
                $token = JWTAuth::fromUser($user);//生成token
                $userData['token'] = $token;
            }
            if (!$user->update($userData)) {
                ReturnJson(false, trans('lang.update_error'));
            }
            //修改收货地址
            if (!empty($userAddessList)) {
                $addressIdList = [];
                $userAddressModel = new UserAddress();
                $user_id = $user['id'];
                foreach ($userAddessList as $userAddress) {
                    $userAddress['user_id'] = $user_id;
                    if (!empty($userAddress['id'])) {
                        $addressIdList[] = $userAddress['id'];
                        $userAddressModel->where('user_id', $user_id)->where('id', $userAddress['id'])->update(
                            $userAddress
                        );
                    } else {
                        $addressIdList[] = $userAddressModel->insertGetId($userAddress);
                    }
                }
                $userAddressModel->where('user_id', $user_id)->whereNotIn('id', $addressIdList)->delete();
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
