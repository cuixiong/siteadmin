<?php
namespace App\Imports;
use Maatwebsite\Excel\Concerns\ToModel;
use Modules\Admin\Http\Models\User;


class UserImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row)
    {
        if(in_array('邮箱',$row)){
            return null;
        }
        if(empty($row[0])){
            return null;
        }
        $model = User::where('email',$row[2])->first();
        $model = empty($model)? new User():$model;
        $model->name = $row[0];
        $model->nickname = $row[1];
        $model->email = $row[2];
        $model->password = $row[3];
        $model->role_id = explode(',',$row[4]);
        $model->status = $row[5];
        $model->mobile = $row[6];
        $model->department_id = $row[7];
        $model->gender = $row[8];
        $model->login_at = $row[9];
        return $model;
    }
}