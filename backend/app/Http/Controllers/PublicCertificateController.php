<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Public (no login) certificate validation. Only exposes basic, non-sensitive data.
 */
class PublicCertificateController extends Controller
{
    public function show(string $code, ActivityLogger $logger): JsonResponse
    {
        $code = Str::limit(strtoupper(trim($code)), 40, '');
        $certificate = Certificate::where('code', $code)->first();

        $logger->log(
            'certificate.validated',
            "Validation lookup for {$code}",
            $certificate,
            ['code' => $code, 'found' => (bool) $certificate],
        );

        if (! $certificate) {
            return response()->json([
                'valid' => false,
                'status' => 'not_found',
                'message' => 'No certificate was found for this code.',
            ], 404);
        }

        return response()->json([
            'valid' => $certificate->isValid(),
            'status' => $certificate->status,
            'certificate' => [
                'code' => $certificate->code,
                'participant_name' => $certificate->participant_name,
                'course_name' => $certificate->course_name,
                'workload_hours' => $certificate->workload_hours,
                'institution_name' => $certificate->institution_name,
                'issued_at' => $certificate->issued_at->format('Y-m-d'),
            ],
        ]);
    }
}
