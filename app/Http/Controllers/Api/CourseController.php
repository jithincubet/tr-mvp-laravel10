<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Course Controller
 * Manages courses used in billing records.
 * Note: Course IDs are user-supplied.
 */
class CourseController extends BaseController
{
    /**
     * GET /api/v1/billing/courses
     */
    public function index(): JsonResponse
    {
        return $this->success(Course::all());
    }

    /**
     * POST /api/v1/billing/courses
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|unique:b_courses,id',
            'name' => 'required|string|max:255',
        ]);

        $course = Course::create($validated);

        return $this->success($course, 'Course created successfully', 201);
    }

    /**
     * PUT /api/v1/billing/courses/{id}
     */
    public function update(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        $course->update($validated);

        return $this->success($course, 'Course updated successfully');
    }

    /**
     * DELETE /api/v1/billing/courses/{id}
     */
    public function destroy(Course $course): JsonResponse
    {
        $course->delete();

        return $this->success(null, 'Course deleted successfully');
    }
}