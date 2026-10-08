<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Journal;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class GenerateQuizJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $journalId;

    /**
     * Create a new job instance.
     */
    public function __construct($journalId)
    {
        $this->journalId = $journalId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $journal = Journal::findOrFail($this->journalId);

        if(!$journal){
            return;
        }

        $topic  = $journal->topic_description ?? 'Materi Umum';
        $scriptPath = base_path('script/quiz_generator.py');

        $process = new Process(['python', $scriptPath, $topic]);
        $process->run();

        if(!$process->isSuccessful()){
            Log::error('Quiz Generator Error: ' . $process->getErrorOutput());
            return;
        }

        $output = $process->getOutput();
        $quizData = json_decode($output, true);

        if(is_array($quizData)){
            $quiz = Quiz::create([
                'journal_id' => $journal->id,
                'title' => 'Kuis: ' . ($journal->topic_description ?? 'Materi Pembelajaran'),
                'status' => 'draft'
            ]);

            foreach ($quizData as $item) {
                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_text' => $item['question'],
                    'options' => $item['options'],
                    'correct_answer' => $item['answer']
                ]);
            }
        }
    }
}
