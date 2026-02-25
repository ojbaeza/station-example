<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Workflow\CreateWorkflowDefinition;
use App\Actions\Workflow\DeleteWorkflowDefinition;
use App\Actions\Workflow\ListWorkflowDefinitions;
use App\Actions\Workflow\RunWorkflowDefinition;
use App\Actions\Workflow\UpdateWorkflowDefinition;
use App\Models\WorkflowDefinition;
use App\Rules\ValidJobClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class WorkflowDefinitionController extends Controller
{
    public function index(ListWorkflowDefinitions $action): JsonResponse
    {
        return response()->json($action->handle());
    }

    public function store(Request $request, CreateWorkflowDefinition $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:station_workflow_definitions,name',
            'description' => 'nullable|string|max:1000',
            'timeout' => 'required|integer|min:1|max:86400',
            'connection' => 'nullable|string|max:100',
            'steps' => 'required|array|min:1',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.job_class' => ['nullable', 'string', 'max:500', new ValidJobClass()],
            'steps.*.dependencies' => 'nullable|array',
            'steps.*.dependencies.*' => 'string',
            'steps.*.parallel' => 'nullable|boolean',
            'steps.*.jobs' => 'nullable|array',
            'steps.*.jobs.*.name' => 'required_with:steps.*.parallel|string|max:255',
            'steps.*.jobs.*.job_class' => ['nullable', 'string', 'max:500', new ValidJobClass()],
        ]);

        $result = $action->handle($validated);

        return response()->json([
            'success' => true,
            'definition' => $result,
        ]);
    }

    public function update(Request $request, string $name, UpdateWorkflowDefinition $action): JsonResponse
    {
        // Need the definition ID for unique validation
        $definition = WorkflowDefinition::where('name', $name)->first();

        if (!$definition) {
            return response()->json(['success' => false, 'error' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'name' => "required|string|max:255|unique:station_workflow_definitions,name,{$definition->id}",
            'description' => 'nullable|string|max:1000',
            'timeout' => 'required|integer|min:1|max:86400',
            'connection' => 'nullable|string|max:100',
            'steps' => 'required|array|min:1',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.job_class' => ['nullable', 'string', 'max:500', new ValidJobClass()],
            'steps.*.dependencies' => 'nullable|array',
            'steps.*.dependencies.*' => 'string',
            'steps.*.parallel' => 'nullable|boolean',
            'steps.*.jobs' => 'nullable|array',
            'steps.*.jobs.*.name' => 'required_with:steps.*.parallel|string|max:255',
            'steps.*.jobs.*.job_class' => ['nullable', 'string', 'max:500', new ValidJobClass()],
        ]);

        $result = $action->handle($name, $validated);

        if ($result === null) {
            return response()->json(['success' => false, 'error' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'definition' => $result,
        ]);
    }

    public function destroy(string $name, DeleteWorkflowDefinition $action): JsonResponse
    {
        return response()->json([
            'success' => $action->handle($name),
        ]);
    }

    public function run(Request $request, RunWorkflowDefinition $action): JsonResponse
    {
        $validated = $request->validate([
            'definition' => 'required|string|max:255',
        ]);

        try {
            $result = $action->handle($validated['definition']);

            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'error' => "Definition '{$validated['definition']}' not found",
                ], 404);
            }

            return response()->json(['success' => true, ...$result]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $this->safeErrorMessage($e),
            ], 500);
        }
    }
}
