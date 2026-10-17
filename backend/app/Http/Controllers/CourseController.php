<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CourseController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return Course::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereLike('name', '%'.$request->string('search').'%');
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function store(Request $request): JsonResponse
    {
        $course = Course::create($this->validated($request));
        $this->logger->log('course.created', "Course {$course->name} created", $course);

        return response()->json($course, 201);
    }

    public function show(Course $course): Course
    {
        return $course;
    }

    public function update(Request $request, Course $course): Course
    {
        $course->update($this->validated($request));
        $this->logger->log(
            'course.updated',
            "Course {$course->name} updated",
            $course,
            ['changes' => Arr::except($course->getChanges(), ['updated_at'])],
        );

        return $course;
    }

    public function destroy(Course $course): JsonResponse
    {
        $this->logger->log('course.deleted', "Course {$course->name} deleted", $course);
        $course->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'workload_hours' => ['required', 'integer', 'min:1', 'max:2000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
        ]);
    }
}
