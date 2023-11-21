<?php
namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\CrudController;
class DatabaseController extends CrudController
{
    // jump to ip phpmyadmin
    public function HrefMyAdmin(Request $request)
    {
        if($request->id){
            $database = \Modules\Admin\Http\Models\Database::find($request->id);
            if($database){
                $param = [
                    'auth_type' => 'config',
                    'host' => $database->ip,
                    'port' => $database->port,
                    'user' => $database->username,
                    'password' => $database->password,
                    'db' => $database->name,
                ];
                $param = http_build_query($param);
                $ip = $_SERVER['SERVER_ADDR'];
                var_dump($ip);die;
                $url = "http://$ip:888/".env('PHPMYADMIN_URL')."/?".$param;
                return redirect($url);
            }
        }
    }
}