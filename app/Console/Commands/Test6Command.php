<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
class Test3Command extends Command
{
    protected $signature = 'Test6';
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
        $RootPath = public_path().'/'.'6.txt';
        file_put_contents($RootPath,"\r".date('Y-m-d H:i:s',time())."=====command",FILE_APPEND);
    }
}