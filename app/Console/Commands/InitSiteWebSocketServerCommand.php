<?php

namespace App\Console\Commands;

use App\WebSocket\InitSite;
use Illuminate\Console\Command;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class InitSiteWebSocketServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the WebSocket server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        // ob_end_clean();
        // 禁用输出缓冲
        // ob_implicit_flush(1);
        
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new InitSite()
                )
            ),
            8080
        );
        
        echo "WebSocket server started on port 8080\n";
        $server->run();
    }
}
