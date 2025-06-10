<?php

namespace Modules\Site\Http\Models;

use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Models\Base;

class PostSubjectStrategy extends Base
{
    protected $table = 'post_subject_strategy';

    // 设置允许入库字段,数组形式
    protected $fillable = ['type', 'category_ids', 'status', 'sort', 'updated_by', 'created_by'];

    protected $attributes = [
        'status' => 1,
        'sort' => 100,
    ];

    const TYPE_ASSIGN = 1; // 分配
    // const TYPE_MoveInCommon = 2; // 移入公客

    public static function getTypeList()
    {
        return [
            self::TYPE_ASSIGN => '分配公共课题',
            // self::TYPE_MoveInCommon => '移入公客',
        ];
    }


    /**
     * 执行分配策略
     */
    public function assignStrategy($type, $config)
    {
        $subjectType = PostSubject::TYPE_POST_SUBJECT; // 课题类型
        $acceptStatus = 0;  // 领取状态
        $categoryIds = [];
        if (!empty($config['category_ids'])) {
            $categoryIds = explode(',', $config['category_ids'] ?? '');
        } else {
            $categoryIds =  [];
        }

        // 查询用户 
        $userData = PostSubjectStrategyUser::from((new PostSubjectStrategyUser)->getTable() . ' as pssu')
            ->select(['pssu.id', 'pssu.user_id', 'pssu.num',])
            ->where('pssu.strategy_id', $config['id'])
            ->orderBy('pssu.sort', 'asc')
            ->get()
            ->toArray();

        if (!$userData) {
            ReturnJson(false, '缺少分配对象');
        }
        
        
        $queryCount = 0;
        $baseQuery =  PostSubject::query()->select(['id', 'name', 'keywords'])
            ->whereIn('product_category_id', $categoryIds)
            ->where('type', $subjectType)
            ->where('accept_status', $acceptStatus);

        foreach ($userData as $key => $userItem) {
            $nickname = User::query()->select(['nickname'])->where('id', $userItem['user_id'])->value('nickname') ?? '未知';
            $userData[$key]['username'] = $nickname;
            $queryCount += $userItem['num'];
        }


        if ($type == 1) {
            $count = $baseQuery->count();
            $text = [];
            $text[] = '查询符合条件的未领取课题数量为: <span style="color:#ff0000;">' . $count . '</span>';
            $text[] = '截取最新<span style="color:#ff0000;">' . $queryCount . '</span>条记录随机分配：';
            $text[] = '------------------------------------';

            if ($queryCount > $count) {
                $text[] = '<span style="color:#ff0000;">剩余数量不足以分配</span>';
                ReturnJson(false, implode("<br />", $text));
            } else {
                foreach ($userData as $key => $userItem) {
                    $text[] = $userItem['username'] . '分配 <span style="color:#ff0000;">' . $userItem['num'] . '</span> 条记录';
                }
            }

            ReturnJson(true, implode("<br />", $text));
        } elseif ($type == 2) {

            $postSubjectData = $baseQuery->limit($queryCount)->orderBy('id','desc')->get()?->toArray()??[];
            $postSubjectData = array_column($postSubjectData, null, 'id');
            $idsArray = array_keys($postSubjectData);

            $text = [];
            if ($queryCount > count($postSubjectData)) {
                $text[] = '<span style="color:#ff0000;">剩余数量不足以分配</span>';
                ReturnJson(false, implode("<br />", $text));
            }

            // 打乱顺序并分割数据,执行分配
            shuffle($idsArray);
            $start = 0;
            $isExist = [];
            $subjectSuccess = 0;
            $subjectIngore = 0;
            $details = [];
            $ingoreDetails = [];
            foreach ($userData as $key => $userItem) {
                if (
                    empty($userItem['user_id']) || !($userItem['user_id'] > 0) ||
                    empty($userItem['num']) || !($userItem['num'] > 0) ||
                    in_array($userItem['user_id'], $isExist)
                ) {
                    continue;
                }
                $isExist[] = $userItem['user_id'];
                // 截取数组片段
                $idSegment = array_slice($idsArray, $start, $userItem['num']);

                // 课题的领取需要读取过滤列表
                $filterKeywordsData = PostSubjectFilter::query()->select(['keywords'])->where('user_id', $userItem['user_id'])->pluck('keywords')?->toArray() ?? [];
                $newIdSegment = [];
                foreach ($idSegment as $key => $subject_id) {
                    $tempKeywords = $postSubjectData[$subject_id]['keywords'] ?? '';
                    if (!empty($tempKeywords) && !in_array($tempKeywords, $filterKeywordsData)) {
                        // unset();
                        $newIdSegment[] = $subject_id;
                        $subjectSuccess++;
                        $details[] = '【' . $userItem['username'] . '】【编号' . $postSubjectData[$subject_id]['id'] . '】' . $postSubjectData[$subject_id]['name'];
                    } else {
                        $subjectIngore++;
                        $tempIngoreText = ' <span style="color:#ff0000;">过滤</span>';
                        $ingoreDetails[] = '【' . $userItem['username'] . '】【编号' . $postSubjectData[$subject_id]['id'] . '】' . $postSubjectData[$subject_id]['name'] . $tempIngoreText;
                    }
                }

                // 领取
                $updateData = [
                    'accepter' => $userItem['user_id'],
                    'accept_time' => time(),
                    'accept_status' => 1,
                    'updated_by' => $userItem['user_id'],
                ];
                // return $updateData;
                PostSubject::query()->whereIn("id", $newIdSegment)->update($updateData);

                // 更新起始位置
                $start += $userItem['num'];
            }

            $log = new PostSubjectLog();
            $logData['type'] = PostSubjectLog::POST_SUBJECT_STRATEGY_ACCEPT;
            $logData['success_count'] = $subjectSuccess;
            $logData['ingore_count'] = $subjectIngore;
            $logData['details'] = '';
            $logData['details'] .= date('Y-m-d H:i:s', time()) . ' 策略分配' . "\n";
            $logData['details'] .= '成功领取' . $subjectSuccess . '个课题, ' . '有' . $subjectIngore . '个课题被黑名单过滤' . "\n";
            $logData['details'] .= implode("\n", $details);
            $logData['ingore_details'] = implode("\n", $ingoreDetails);
            $log->create($logData);

            ReturnJson(true, '完成');
        }
    }
}
