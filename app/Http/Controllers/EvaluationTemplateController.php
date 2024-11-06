<?php

namespace App\Http\Controllers;

use App\Models\EvaluationTemplate;
use App\Models\Management;
use Illuminate\Http\Request;
use App\ApiCode;
use Illuminate\Support\Facades\DB;

class EvaluationTemplateController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'management_id' => 'required|exists:management,id',
            'type' => 'required|in:self,peer,cross',
            'name' => 'required|string|max:255',
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.criteria' => 'required|array|min:1',
            'sections.*.criteria.*.name' => 'required|string|min:5|max:255',
            'sections.*.criteria.*.description' => 'nullable|string',
        ]);

        $management = Management::findOrFail($request->management_id);

        if ($management->evaluationTemplates()->where('type', $request->type)->exists()) {
            return $this->respondBadRequest(ApiCode::TEMPLATE_ALREADY_EXISTS);
        }

        DB::beginTransaction();

        try {
            $template = EvaluationTemplate::create([
                'management_id' => $request->management_id,
                'type' => $request->type,
                'name' => $request->name,
            ]);

            foreach ($request->sections as $index => $sectionData) {
                $section = $template->sections()->create([
                    'title' => $sectionData['title'],
                    'order' => $index,
                ]);

                foreach ($sectionData['criteria'] as $criterionIndex => $criterionData) {
                    $section->criteria()->create([
                        'name' => $criterionData['name'],
                        'description' => $criterionData['description'] ?? null,
                        'order' => $criterionIndex,
                    ]);
                }
            }

            DB::commit();

            return $this->respond(['template' => $template->load('sections.criteria')], 'Evaluation template created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondBadRequest(ApiCode::TEMPLATE_CREATION_FAILED);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sections' => 'required|array|min:1',
            'sections.*.id' => 'sometimes|exists:template_sections,id',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.criteria' => 'required|array|min:1',
            'sections.*.criteria.*.id' => 'sometimes|exists:template_criteria,id',
            'sections.*.criteria.*.name' => 'required|string|min:5|max:255',
            'sections.*.criteria.*.description' => 'nullable|string',
        ]);

        $template = EvaluationTemplate::findOrFail($id);

        DB::beginTransaction();

        try {
            $template->update(['name' => $request->name]);

            $existingSectionIds = $template->sections->pluck('id')->toArray();
            $updatedSectionIds = [];

            foreach ($request->sections as $index => $sectionData) {
                if (isset($sectionData['id'])) {
                    $section = $template->sections()->findOrFail($sectionData['id']);
                    $section->update([
                        'title' => $sectionData['title'],
                        'order' => $index,
                    ]);
                } else {
                    $section = $template->sections()->create([
                        'title' => $sectionData['title'],
                        'order' => $index,
                    ]);
                }

                $updatedSectionIds[] = $section->id;

                $existingCriteriaIds = $section->criteria->pluck('id')->toArray();
                $updatedCriteriaIds = [];

                foreach ($sectionData['criteria'] as $criterionIndex => $criterionData) {
                    if (isset($criterionData['id'])) {
                        $criterion = $section->criteria()->findOrFail($criterionData['id']);
                        $criterion->update([
                            'name' => $criterionData['name'],
                            'description' => $criterionData['description'] ?? null,
                            'order' => $criterionIndex,
                        ]);
                    } else {
                        $criterion = $section->criteria()->create([
                            'name' => $criterionData['name'],
                            'description' => $criterionData['description'] ?? null,
                            'order' => $criterionIndex,
                        ]);
                    }

                    $updatedCriteriaIds[] = $criterion->id;
                }

                // Delete criteria that are not in the updated list
                $section->criteria()->whereNotIn('id', $updatedCriteriaIds)->delete();
            }

            // Delete sections that are not in the updated list
            $template->sections()->whereNotIn('id', $updatedSectionIds)->delete();

            DB::commit();

            return $this->respond(['template' => $template->fresh()->load('sections.criteria')], 'Evaluation template updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondBadRequest(ApiCode::TEMPLATE_UPDATE_FAILED);
        }
    }

    public function show($id)
    {
        $template = EvaluationTemplate::with('sections.criteria')->findOrFail($id);
        return $this->respond(['template' => $template]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'management_id' => 'required|exists:management,id',
        ]);

        $templates = EvaluationTemplate::where('management_id', $request->management_id)
            ->with('sections.criteria')
            ->get();

        return $this->respond(['templates' => $templates]);
    }
}
