<?php

namespace App\Console\Commands;

use App\Models\Product\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckInvoices extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if there are any expired invoices.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        //
        $invoices = Invoice::whereNotIn('status', ['COMPLETED', 'CANCELLED'])->get();
        $count = 0;
        foreach ($invoices as $invoice) {
            // check if its been 3 hours since the invoice was created
            if ($invoice->status == 'PAYER_ACTION_REQUIRED' && Carbon::now()->diffInHours($invoice->created_at) >= 3) {
                // cancel the invoice
                $invoice->update([
                    'status' => 'CANCELLED',
                ]);
                $count++;
            } elseif (Carbon::now()->diffInHours($invoice->created_at) >= 24) {
                // cancel the invoice
                $invoice->update([
                    'status' => 'CANCELLED',
                ]);
                $count++;
            }
        }
        $this->info('Cancelled '.$count.' invoices.');
    }
}
