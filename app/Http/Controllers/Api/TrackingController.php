<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackingController extends Controller
{
    protected TrackingService $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Log user's current location
     */
    public function logLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'battery_level' => 'nullable|integer|between:0,100',
            'speed' => 'nullable|numeric|min:0',
            'accuracy' => 'nullable|numeric|min:0',
            'type' => 'nullable|in:route,ping,check_in,check_out',
            'activity_type' => 'nullable|string',
            'is_mock_location' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }

            $trackingLog = $this->trackingService->logLocation($user, $request->all());

            if (!$trackingLog) {
                return response()->json(['success' => false, 'message' => 'فشل في تسجيل الموقع'], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الموقع بنجاح',
                'data' => ['id' => $trackingLog->id, 'tracked_at' => $trackingLog->tracked_at],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الموقع',
            ], 500);
        }
    }

    /**
     * Batch upload offline location logs
     */
    public function batchUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'locations' => 'required|array|min:1',
            'locations.*.latitude' => 'required|numeric|between:-90,90',
            'locations.*.longitude' => 'required|numeric|between:-180,180',
            'locations.*.tracked_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        if (!$user) return response()->json(['success' => false], 401);

        $successCount = 0;
        foreach ($request->locations as $locationData) {
            if ($this->trackingService->logLocation($user, $locationData)) $successCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "تم رفع {$successCount} سجل بنجاح",
            'data' => ['success_count' => $successCount, 'total_count' => count($request->locations)],
        ]);
    }
}
