<?php

namespace Modules\Site\Http\Models;


class Country extends Base
{
    protected $table = 'countrys';

    /**
     * 获取某个国家的对应语言的名称
     */
    public static function getCountryName($country_id, $language = null)
    {

        $data = Country::where('status', 1)->where('id', $country_id)->value('data');
        $name = '';
        if ($data) {
            $data = json_decode($data, true);
            if (!$language) {
                $language = request()->HeaderLanguage ?? '';
            }
            $sitename = getSiteName();
            if(in_array($sitename , ['mrrs' , 'yhen' , 'qyen', 'lpien' , 'mmgen' , 'giren'])){
                $language = 'en';
            }
            switch ($language) {
                case 'en':
                    $name = $data['en'];
                    break;

                case 'zh':
                    $name = $data['zh-cn'];
                    break;

                case 'jp':
                    $name = $data['jp'];
                    break;

                default:
                    $name = $data['en'];
                    break;
            };
        }
        return $name;
    }


}
