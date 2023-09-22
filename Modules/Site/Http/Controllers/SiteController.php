<?php

namespace Modules\Site\Http\Controllers;
use Illuminate\Routing\Controller;
use Modules\Site\Http\Models\UsersModel;
use Modules\Admin\Http\Models\Site;
use Illuminate\Support\Facades\DB;
class SiteController extends Controller
{
    public function index()
    {
        var_dump(123);
        $model = new UsersModel();
        $users = $model->get();
        var_dump($users);
    }

    public function user()
    {
        $res = DB::table('users')->get()->toArray();
        // var_dump($res);
        $site = (new Site())->get()->toArray();
        $site1 = DB::connection('mysql')->table('site')->get()->toArray();
        var_dump($site1);
    }
}
