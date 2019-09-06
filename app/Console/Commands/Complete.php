<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Complete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complete:auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动结算';

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
     *
     * @return mixed
     */
    public function handle()
    {
        //
    }
}
