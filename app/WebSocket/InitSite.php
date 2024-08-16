<?php

namespace App\WebSocket;

use Modules\Admin\Http\Models\Database;
use Modules\Admin\Http\Models\Server;
use Modules\Admin\Http\Models\Site;
use phpseclib3\Net\SSH2;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class InitSite implements MessageComponentInterface
{

    public function onOpen(ConnectionInterface $conn)
    {
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // 接收参数
        $data = json_decode($msg, true);
        $siteId = $data['site_id'] ?? '';
        $stepCode = $data['step'] ?? '';
        $param = $data['param'] ?? '';
        if (!empty($param) && !is_array($param)) {
            $param = $param ? json_decode($param, true) : [];
        }
        // 创建者ID
        // $created_by = request()->user->id;
        // $from->send($created_by);
        $created_by = 1;
        //获取站点配置
        $site = Site::findOrFail($siteId);
        //获取服务器配置
        $server = Server::find($site->server_id);
        //获取数据库配置
        $database = Database::find($site->database_id);
        $checkParamEmpty = [
            'server_model_empty'             => $server,
            'database_model_empty'           => $database,
            'site_name_empty'                => $site->name ?? '',
            'site_api_repository_empty'      => $site->api_repository ?? '',
            'site_frontend_repository_empty' => $site->frontend_repository ?? '',
            'site_api_path_empty'            => $site->api_path ?? '',
            'site_frontend_path_empty'       => $site->frontend_path ?? '',
            'site_domain_empty'              => $site->domain ?? '',
            'server_ip_empty'                => $server->ip ?? '',
            'server_username_empty'          => $server->username ?? '',
            'server_password_empty'          => $server->password ?? '',
            'server_bt_link_empty'           => $server->bt_link ?? '',
            'server_bt_apisecret_empty'      => $server->bt_apisecret ?? '',
            'database_ip_empty'              => $database->ip ?? '',
            'database_username_empty'        => $database->username ?? '',
            'database_password_empty'        => $database->password ?? '',
        ];
        // 判断参数是否为空
        foreach ($checkParamEmpty as $key => $value) {
            if (empty($value)) {
                // 发送结果回客户端
                $from->send(!empty(trans('lang.' . $key)) ? trans('lang.' . $key) : trans('lang.param_empty'));
                return;
            }
        }

        // $ssh = new SSH2($server->ip);
        // $ssh->login($server->username, $server->password);
        // $command = 'cd ' .  $site->api_path . ' &&  composer install ';
        // // $command = 'cd ' .  $site->frontend_path . ' &&  npm i ';
        // $ssh->exec($command, function($output) use ($from){
        //     $output = $this->removeAnsiControlChars($output);

        //     file_put_contents('C:\\Users\\Administrator\\Desktop\\zqy.txt',$output,FILE_APPEND);
        //     // $from->send('output'.$output);
        //     $from->send($output);

        //     echo $output; // 这里可以处理实时输出，比如存储到日志或前端显示
        //     // return true;
        //     // return $output;
        // });
        // $from->send('Done');

        try {
            $initWebsiteStep = Site::getInitWebsiteStep(true);
            if ($initWebsiteStep['commands'] && in_array($stepCode, $initWebsiteStep['commands'])) {
                $output = Site::executeRemoteCommand($site, $stepCode, $server, $database,  ['created_by' => $created_by]);
            } elseif ($initWebsiteStep['btPanelApi'] && in_array($stepCode, $initWebsiteStep['btPanelApi'])) {
                $option['created_by'] = $created_by;
                if (isset($param['private_key'])) {
                    $option['private_key'] = $param['private_key'];
                }
                if (isset($param['csr'])) {
                    $option['csr'] = $param['csr'];
                }
                $output = Site::invokeBtApi($site, $server, $stepCode, $option);
                // ReturnJson(FALSE, trans('lang.request_success'), $output);
            }
        } catch (\Throwable $th) {
            $from->send($th->getMessage());
            $from->send($th->getTraceAsString());
            return;
        }
        if (!$output['result']) {
            $from->send(!empty($output['message']) ? $output['message'] : $output['output']);
            return;
        }
        $from->send(json_encode($output));
        return;

    }

    public function onClose(ConnectionInterface $conn)
    {
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        echo "An error has occurred: {$e->getTraceAsString()}\n";
        $conn->close();
    }
}
