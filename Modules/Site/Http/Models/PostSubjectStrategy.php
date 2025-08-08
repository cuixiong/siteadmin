<?php

namespace Modules\Site\Http\Models;

use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Controllers\TemplateController;
use Modules\Site\Http\Models\Base;

class PostSubjectStrategy extends Base
{
    protected $table = 'post_subject_strategy';

    // 设置允许入库字段,数组形式
    protected $fillable = ['type', 'category_ids', 'version', 'status', 'sort', 'updated_by', 'created_by'];

    protected $attributes = [
        'status' => 1,
        'sort' => 100,
    ];

    const TYPE_ASSIGN = 1; // 分配
    const TYPE_DIMISSION = 2; // 离职归档

    public static function getTypeList()
    {
        return [
            self::TYPE_ASSIGN => '分配公共课题',
            // self::TYPE_MoveInCommon => '移入公客',
            self::TYPE_DIMISSION => '离职归档',
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
        $versionArray = [];
        if (!empty($config['version'])) {
            $versionArray = explode(',', $config['version'] ?? '');
        } else {
            $versionArray =  [];
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
            ->where('type', $subjectType)
            ->where('accept_status', $acceptStatus);

        if ($categoryIds && count($categoryIds) > 0) {
            $baseQuery->whereIn('product_category_id', $categoryIds);
        }
        if ($versionArray && count($versionArray) > 0) {
            $baseQuery->whereIn('version', $versionArray);
        }

        foreach ($userData as $key => $userItem) {
            $nickname = User::query()->select(['nickname'])->where('id', $userItem['user_id'])->value('nickname') ?? '未知';
            $userData[$key]['username'] = $nickname;
            $queryCount += $userItem['num'];
        }


        if ($type == 1) {
            $count = $baseQuery->count();
            $returnData = [];
            $returnData['type'] = PostSubjectStrategy::TYPE_ASSIGN;
            $returnData['all_count'] = $count;
            $returnData['query_count'] = $queryCount;
            $returnData['user'] = [];

            if ($queryCount > $count) {
                ReturnJson(false, '剩余数量不足以分配', $returnData);
            } else {
                foreach ($userData as $key => $userItem) {
                    $returnData['user'][] = [
                        'user_id' => $userItem['user_id'],
                        'username' => $userItem['username'],
                        'num' => $userItem['num'],
                    ];
                }
            }

            ReturnJson(true, '返回数量', $returnData);
        } elseif ($type == 2) {

            $postSubjectData = $baseQuery->limit($queryCount)->orderBy('id', 'desc')->get()?->toArray() ?? [];
            $postSubjectData = array_column($postSubjectData, null, 'id');
            $idsArray = array_keys($postSubjectData);

            $returnData = [];
            $returnData['all_count'] = count($postSubjectData);
            $returnData['query_count'] = $queryCount;
            if ($queryCount > count($postSubjectData)) {
                ReturnJson(false, '剩余数量不足以分配', $returnData);
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

            ReturnJson(true, '执行完成');
        }
    }


    /**
     * 执行归档策略
     */
    public function dimissionStrategy($type, $config)
    {
        $categoryIds = [];
        if (!empty($config['category_ids'])) {
            $categoryIds = explode(',', $config['category_ids'] ?? '');
        } else {
            $categoryIds =  [];
        }
        $versionArray = [];
        if (!empty($config['version'])) {
            $versionArray = explode(',', $config['version'] ?? '');
        } else {
            $versionArray =  [];
        }


        // 查询归档用户,只要一个
        $userData = PostSubjectStrategyUser::from((new PostSubjectStrategyUser)->getTable() . ' as pssu')
            ->select(['pssu.id', 'pssu.user_id', 'pssu.num',])
            ->where('pssu.strategy_id', $config['id'])
            ->orderBy('pssu.sort', 'asc')
            ->first();

        if (!$userData) {
            ReturnJson(false, '缺少归档对象');
        }
        $userData = $userData->toArray();
        $userData['nickname'] = User::query()->select(['nickname'])->where('id', $userData['user_id'])->value('nickname') ?? '未知';


        // 查询状态关闭(离职)的发帖用户
        $dimissionUserData = (new TemplateController())->getSitePostUser(0);
        if (!$dimissionUserData || count($dimissionUserData) == 0) {
            ReturnJson(false, '发帖用户群组中无离职人员');
        }

        $baseQuery =  PostSubject::query()
            ->select(['id', 'name', 'keywords']);

        if ($categoryIds && count($categoryIds) > 0) {
            $baseQuery->whereIn('product_category_id', $categoryIds);
        }
        if ($versionArray && count($versionArray) > 0) {
            $baseQuery->whereIn('version', $versionArray);
        }

        if ($type == 1) {

            $returnData = [];
            $returnData['type'] = PostSubjectStrategy::TYPE_DIMISSION;

            $propagateCount = 0;
            $unPropagateCount = 0;
            $returnData['user'] = [];
            foreach ($dimissionUserData as $key => $userItem) {
                $tempPropagateCount = (clone $baseQuery)->where('accepter', $userItem['value'])->where('propagate_status', 1)->count();
                $tempUnPropagateCount = (clone $baseQuery)->where('accepter', $userItem['value'])->where('propagate_status', 0)->count();
                $returnData['user'][] = [
                    'user_id' => $userItem['value'],
                    'username' => $userItem['label'],
                    'propagate_count' => $tempPropagateCount ?? 0,
                    'unpropagate_count' => $tempUnPropagateCount ?? 0,
                ];
                $propagateCount += $tempPropagateCount ?? 0;
                $unPropagateCount += $tempUnPropagateCount ?? 0;
            }

            $returnData['propagate_count'] = $propagateCount;
            $returnData['unPropagate_count'] = $unPropagateCount;

            ReturnJson(true, '返回数量', $returnData);
        } elseif ($type == 2) {

            // 已宣传课题进入归档账号
            $updateData = [
                'accepter' => $userData['id'],
                'accept_time' => time(),
                'accept_status' => 1,
                'updated_by' => $userData['id'],
            ];
            $propagateCount = (clone $baseQuery)
                ->whereIn('accepter', array_column($dimissionUserData, 'value'))
                ->where('propagate_status', 1)
                ->update($updateData);

            // 未宣传课题进入公客
            $updateData = [
                'accepter' => null,
                'accept_time' => null,
                'accept_status' => 0,
                'updated_by' => $userData['id'],
            ];
            $unPropagateCount = (clone $baseQuery)
                ->whereIn('accepter', array_column($dimissionUserData, 'value'))
                ->where('propagate_status', 0)
                ->update($updateData);
    

            $log = new PostSubjectLog();
            $logData['type'] = PostSubjectLog::POST_SUBJECT_STRATEGY_DIMISSION;
            $logData['success_count'] = $propagateCount + $unPropagateCount;
            $logData['ingore_count'] = 0;
            $logData['details'] = '';
            $logData['details'] .= date('Y-m-d H:i:s', time()) . ' 策略归档' . "\n";
            $logData['details'] .= $unPropagateCount . '个课题转入公客, ' . $propagateCount . '个课题转入 【'. $userData['nickname'] .'】'. "\n";
            $logData['ingore_details'] = '';
            $log->create($logData);

            ReturnJson(true, '执行完成');
        }
    }
}
