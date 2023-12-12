<?php

namespace App\Console\Commands;

use App\Models\Comment;
use Illuminate\Console\Command;

class UpdateProducts extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates old "cash shop" to new "products" system.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {

    }
}
