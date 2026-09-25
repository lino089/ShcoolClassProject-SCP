<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ScheduleImport;

class ParseSchedulePdfJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importId;

    public function __construct($importId)
    {   
        $this->importId = $importId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $import = ScheduleImport::find($this->importId);;

        if($import) {
            $import->update(['status' => 'parsing']);
        }
    }
}
