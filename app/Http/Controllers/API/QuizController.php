<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Jobs\GenerateQuizJob;
use App\Models\Quiz;
use App\Models\Attendance;
use App\Models\QuizSubmision;
use App\Models\QuizQuestion;

class QuizController extends Controller
{
    public function generate(Request $request, $journalId){
        $journal = Journal::with('schedule')->findOrFail($journalId);
        $userId = $request->user()->id;

        $isTeacher = ($journal->schedule->teacher_id === $userId);
        $isSubstitute = ($journal->substitute_teacher_id === $userId);

        if (!$isTeacher && !$isSubstitute){
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        if(empty($journal->topic_description)){
            return response()->json([
                'success' => false,
                'message' => 'Jurnal belum memiliki materi pembahasan untuk dijadikan kuis.'
            ], 422);
        }

        GenerateQuizJob::dispatch($journal->id);

        return response()->json([
            'success' => true,
            'message' => 'Pembuatan kuis otomatis sedang diproses di latar belakang.'
        ], 202);
    }


    public function publish($id, Request $request){
        $quiz = Quiz::with('journal.schedule')->findOrFail($id);

        if($quiz->journal->schedule->teacher_id !== $request->user()->id){
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $quiz->update(['status' => 'published']);

        return response()->json([
            'success' => true,
            'message' => 'Kuis berhasil dipublikasikan.',
            'data' => $quiz
        ], 200);
    }

    public function show($id, Request $request){
        $quiz = Quiz::with(['questions', 'journal'])->findOrFail($id);
        $user = $request->user();

        if($user->role === 'student'){
            if($quiz->status !== 'published'){
                return response()->json([
                    'success' => false,
                    'message' => 'Kuiz belum dibuka' 
                ],402);
            }

            $attendance = Attendance::where('journal_id', $quiz->journal_id)
                ->where('student_id', $user->id)
                ->first();
            
            if(!$attendance || $attendance->status !== 'Hadir'){
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses kuis ini karena tidak berstatus hadir.'
                ], 403);
            }

            $quiz->questions->makeHidden(['correct_answer']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data kuis berhasil diambil.',
            'data' => $quiz
        ], 200);
    }

    public function submit(Request $request, $id){
        $user = $request->user();
        if($user->role !== 'student'){
            return response()->json([
                'success' => false,
                'message' => 'Hanya siswa yang dapat mengumpulkan kuis.'
            ], 403);
        }
        
        $quiz = Quiz::with('journal')->findOrFail($id);

        if($quiz->status !== 'published'){
            return response()->json([
                'success' => false,
                'message' => 'Kuis tidak aktif.'
            ], 422);
        }

        $attendance = Attendance::where('journal_id', $quiz->journal_id)
            ->where('student_id', $user->id)
            ->first();

        if(!$attendance || $attendance->status !== 'Hadir'){
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ]);
        }

        $existing = QuizSubmision::where('quiz_id', $quiz->id)
            ->where('student_id', $user->id)
            ->exists();

        if($existing){
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah mengerjakan kuis ini.'
            ], 422);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.selected_option' => 'required|string|in:A,B,C,D'
        ]);

        $questions = QuizQuestion::where('quiz_id', $quiz->id)->get()->keyBy('id');
        $totalQuestions = $questions->count();
        $correctCount = 0;

        foreach ($request->answers as $ans){
            $qId = $ans['question_id'];
            if(isset($questions[$qId]) && $questions[$qId]->correct_answer == $ans['selected_option']){
                $correctCount++;
            }
        }

        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0;

        $submission = QuizSubmision::create([
            'quiz_id' => $quiz->id,
            'student_id' => $user->id,
            'answers' => json_encode($request->answers),
            'score' => $score
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kuis berhasil dikumpulkan.',
            'data' => [
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctCount,
                'score' => $score
            ]
        ], 200);
    } 
}
