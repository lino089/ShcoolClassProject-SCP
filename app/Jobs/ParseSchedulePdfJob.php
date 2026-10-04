<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ScheduleImport;
use App\Models\ScheduleImportRow;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;
use App\Models\Subject;
use App\Models\User;
use App\Models\Rooms;
use App\Models\Classes;

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
        $import = ScheduleImport::find($this->importId);

        if (!$import) {
            return;
        }

        $import->update(['status' => 'parsing']);

        $fullPathKelas = storage_path('app/private/' . $import->file_path_kelas);
        $fullPathRuangan = storage_path('app/private/' . $import->file_path_ruangan);
        $scriptPath = base_path('script/pdf_parser.py');

        $process = new Process(['python', $scriptPath, $fullPathKelas, $fullPathRuangan]);

        $process->run();

        if (!$process->isSuccessful()) {
            $import->update(['status' => 'failed']);
            Log::error('Python Parser Error: ' . $process->getErrorOutput());
            return;
        }

        $outputString = $process->getOutput();
        $parseData = json_decode($outputString, true);

        if (is_array($parseData)) {
            foreach ($parseData as $row) {
                $subject = Subject::where('name', 'ILIKE', '%' . trim($row['mapel_mentah']) . '%')->first();

                $teacher = User::where('role', 'teacher')
                    ->where('name', 'ILIKE', '%' . trim($row['guru_mentah']) . '%')
                    ->first();

                $classId = 1;
                $roomId = 1;

                $matchStatus = ($subject && $teacher) ? 'matched' : 'unmatched';

                ScheduleImportRow::create([
                    'import_id' => $import->id,
                    'raw_data' => $row,
                    'matched_subject_id' => $subject ? $subject->id : null,
                    'matched_teacher_id' => $teacher ? $teacher->id : null,
                    'matched_class_id' => $classId,
                    'matched_room_id' => $roomId,
                    'day_of_week' => 1,
                    'period_number' => 1,
                    'duration_periods' => 1,
                    'match_status' => $matchStatus
                ]);
            }

            $import->update(['status' => 'preview_ready']);
        } else {
            $import->update(['status' => 'failed']);
        }


    }
}
