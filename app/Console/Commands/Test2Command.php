<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
class Test2Command extends Command
{
    protected $signature = 'Test2';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return int
     */
    public function handle()
    {
        $RootPath = public_path().'/'.'2.txt';
        file_put_contents($RootPath,"\r".date('Y-m-d H:i:s',time())."=====command",FILE_APPEND);
    }
}