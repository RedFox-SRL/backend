<?php

namespace App\Services;

use App\Mail\EvaluationActivationMail;
use App\Mail\EvaluationReminderMail;
use App\Mail\TeacherSummaryMail;
use App\Models\Sprint;
use App\Models\EvaluationPeriod;
use App\Models\StudentEvaluation;
use App\Models\PeerEvaluationAssignment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\ApiCode;
use Exception;

class EvaluationService
{
    public function createAndActivateEvaluations(Sprint $sprint)
    {
        DB::transaction(function () use ($sprint) {
            $this->createEvaluationPeriods($sprint);
            $this->sendActivationNotifications($sprint);
        });
    }

    private function createEvaluationPeriods(Sprint $sprint)
    {
        $management = $sprint->group->management;
        $selfTemplate = $management->evaluationTemplates()->where('type', 'self')->first();
        $peerTemplate = $management->evaluationTemplates()->where('type', 'peer')->first();

        if (!$selfTemplate || !$peerTemplate) {
            return;
        }

        $startDate = $sprint->end_date;
        $endDate = $startDate->copy()->addDays(4)->setTime(22, 0, 0);

        $this->createEvaluationPeriod($sprint, $selfTemplate, 'self', $startDate, $endDate);
        $this->createEvaluationPeriod($sprint, $peerTemplate, 'peer', $startDate, $endDate);
    }

    private function createEvaluationPeriod(Sprint $sprint, $template, $type, $startDate, $endDate)
    {
        $period = EvaluationPeriod::create([
            'sprint_id' => $sprint->id,
            'evaluation_template_id' => $template->id,
            'type' => $type,
            'starts_at' => $startDate,
            'ends_at' => $endDate,
        ]);

        $students = $sprint->group->students;

        if ($type === 'self') {
            foreach ($students as $student) {
                $this->createStudentEvaluation($period, $student, $student);
            }
        } else if ($type === 'peer') {
            $this->assignPeerEvaluation($period, $students);
        }
    }

    private function sendActivationNotifications(Sprint $sprint)
    {
        $students = $sprint->group->students;
        foreach ($students as $student) {
            $evaluations = StudentEvaluation::where('evaluator_id', $student->id)
                ->whereHas('evaluationPeriod', function ($query) use ($sprint) {
                    $query->where('sprint_id', $sprint->id);
                })
                ->get();

            if ($evaluations->isNotEmpty()) {
                Mail::to($student->user->email)->send(new EvaluationActivationMail($evaluations, $student, $sprint));
            }
        }
    }

    public function sendReminders()
    {
        $now = Carbon::now();
        $remindersToSend = StudentEvaluation::where('is_completed', false)
            ->whereHas('evaluationPeriod', function ($query) use ($now) {
                $query->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now);
            })
            ->with(['evaluationPeriod', 'evaluator.user'])
            ->get();

        foreach ($remindersToSend as $evaluation) {
            $activationDate = $evaluation->evaluationPeriod->starts_at;
            $daysSinceActivation = $now->diffInDays($activationDate);

            if ($daysSinceActivation > 0 && $daysSinceActivation <= 3) {
                Mail::to($evaluation->evaluator->user->email)
                    ->send(new EvaluationReminderMail($evaluation));
            }
        }
    }

    private function createStudentEvaluation(EvaluationPeriod $period, Student $evaluator, Student $evaluated = null)
    {
        StudentEvaluation::create([
            'evaluation_period_id' => $period->id,
            'evaluator_id' => $evaluator->id,
            'evaluated_id' => $evaluated ? $evaluated->id : null,
        ]);
    }

    private function assignPeerEvaluation(EvaluationPeriod $period, $students)
    {
        $studentsArray = $students->shuffle()->values()->all();
        $studentCount = count($studentsArray);

        for ($i = 0; $i < $studentCount; $i++) {
            $evaluator = $studentsArray[$i];
            $evaluated = $studentsArray[($i + 1) % $studentCount];

            PeerEvaluationAssignment::create([
                'evaluation_period_id' => $period->id,
                'evaluator_id' => $evaluator['id'],
                'evaluated_id' => $evaluated['id'],
            ]);

            $this->createStudentEvaluation($period, Student::find($evaluator['id']), Student::find($evaluated['id']));
        }
    }

    public function getActiveEvaluations(Student $student)
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $evaluations = StudentEvaluation::where('evaluator_id', $student->id)
            ->whereHas('evaluationPeriod', function ($query) use ($now) {
                $query->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now);
            })
            ->with(['evaluationPeriod.evaluationTemplate.sections.criteria', 'evaluated.user', 'evaluationPeriod.sprint'])
            ->get();

        if ($evaluations->isEmpty()) {
            return ['success' => true, 'message' => 'No se encontraron evaluaciones activas.'];
        }

        $activeEvaluations = $evaluations->filter(function ($evaluation) {
            return !$evaluation->is_completed;
        })->map(function ($evaluation) {
            $data = $evaluation->toArray();
            if ($evaluation->evaluationPeriod->type === 'peer') {
                $data['evaluated_name'] = $evaluation->evaluated->user->name;
                $data['evaluated_last_name'] = $evaluation->evaluated->user->last_name;
            }
            return $data;
        });

        $completedEvaluations = $evaluations->filter(function ($evaluation) {
            return $evaluation->is_completed;
        });

        $result = [
            'active' => $activeEvaluations->values(),
            'completed' => $completedEvaluations->map(function ($evaluation) {
                return [
                    'id' => $evaluation->id,
                    'type' => $evaluation->evaluationPeriod->type,
                    'completed_at' => $evaluation->completed_at ? Carbon::parse($evaluation->completed_at)->format('Y-m-d H:i:s') : null,
                ];
            })->values(),
        ];

        return ['success' => true, 'evaluations' => $result];
    }

    public function submitEvaluation(StudentEvaluation $evaluation, array $responses)
    {
        try {
            $now = Carbon::now();
            $evaluationPeriod = $evaluation->evaluationPeriod;

            if ($now > $evaluationPeriod->ends_at) {
                return ['success' => false, 'error' => ApiCode::EVALUATION_PERIOD_EXPIRED];
            }

            $template = $evaluationPeriod->evaluationTemplate;
            $expectedCriteriaCount = $template->sections->flatMap->criteria->count();

            if (count($responses) !== $expectedCriteriaCount) {
                return ['success' => false, 'error' => ApiCode::INVALID_RESPONSE_COUNT];
            }

            $validCriteriaIds = $template->sections->flatMap->criteria->pluck('id')->toArray();

            DB::transaction(function () use ($evaluation, $responses, $validCriteriaIds) {
                foreach ($responses as $criterionId => $score) {
                    if (!in_array($criterionId, $validCriteriaIds)) {
                        throw new Exception('ID de criterio inválido');
                    }

                    $evaluation->responses()->create([
                        'template_criterion_id' => $criterionId,
                        'score' => $score,
                    ]);
                }

                $evaluation->update([
                    'is_completed' => true,
                    'completed_at' => Carbon::now(),
                ]);

                $this->checkAndSendTeacherSummary($evaluation->evaluationPeriod->sprint);
            });
            return ['success' => true, 'message' => 'Evaluación enviada con éxito.'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => ApiCode::EVALUATION_SUBMISSION_FAILED];
        }
    }

    private function checkAndSendTeacherSummary(Sprint $sprint)
    {
        $allCompleted = $sprint->evaluationPeriods()
            ->with('studentEvaluations')
            ->get()
            ->every(function ($period) {
                return $period->studentEvaluations->every->is_completed;
            });

        if ($allCompleted) {
            $this->sendTeacherSummary($sprint);
        }
    }

    private function sendTeacherSummary(Sprint $sprint)
    {
        $teacher = $sprint->group->management->teacher;

        if ($teacher && $teacher->user) {
            $summary = $this->generateEvaluationSummary($sprint);
            Mail::to($teacher->user->email)->send(new TeacherSummaryMail($sprint, $summary));
        }
    }

    private function generateEvaluationSummary(Sprint $sprint)
    {
        $evaluations = StudentEvaluation::whereHas('evaluationPeriod', function ($query) use ($sprint) {
            $query->where('sprint_id', $sprint->id);
        })->with(['evaluator', 'evaluated', 'responses.templateCriterion', 'evaluationPeriod'])->get();

        $summary = [
            'self' => [],
            'peer' => [],
        ];

        foreach ($evaluations as $evaluation) {
            $type = $evaluation->evaluationPeriod->type;
            $studentId = $evaluation->evaluator_id;

            if (!isset($summary[$type][$studentId])) {
                $summary[$type][$studentId] = [
                    'name' => $evaluation->evaluator->user->name,
                    'evaluations' => [],
                ];
            }

            $evaluationData = [
                'evaluated' => $evaluation->evaluated ? $evaluation->evaluated->user->name : 'Self',
                'scores' => [],
            ];

            foreach ($evaluation->responses as $response) {
                $criterionName = $response->templateCriterion->name;
                $evaluationData['scores'][$criterionName] = $response->score;
            }

            $summary[$type][$studentId]['evaluations'][] = $evaluationData;
        }

        return $summary;
    }
}
