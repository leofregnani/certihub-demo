<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Participant;
use App\Services\ActivityLogger;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CertificateController extends Controller
{
    public function __construct(
        private ActivityLogger $logger,
        private CertificateService $certificates,
    ) {
    }

    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return Certificate::query()
            ->with('issuer:id,name')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(fn ($q) => $q->whereLike('code', $term)
                    ->orWhereLike('participant_name', $term)
                    ->orWhereLike('course_name', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('course_id'), fn ($q) => $q->where('course_id', $request->integer('course_id')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('issued_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('issued_at', '<=', $request->date('to')))
            ->latest('id')
            ->paginate($perPage);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'participant_id' => ['required', 'integer', Rule::exists('participants', 'id')],
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')],
            'issued_at' => ['nullable', 'date'],
        ]);

        $certificate = $this->certificates->issue(
            Participant::findOrFail($data['participant_id']),
            Course::findOrFail($data['course_id']),
            $request->user(),
            isset($data['issued_at']) ? \Illuminate\Support\Carbon::parse($data['issued_at'])->toDateString() : null,
        );

        $this->logger->log(
            'certificate.issued',
            "Certificate {$certificate->code} issued to {$certificate->participant_name}",
            $certificate,
        );

        return response()->json($certificate->load('issuer:id,name'), 201);
    }

    public function show(Certificate $certificate): Certificate
    {
        return $certificate->load('issuer:id,name');
    }

    public function pdf(Certificate $certificate)
    {
        $this->logger->log('certificate.downloaded', "PDF of {$certificate->code} downloaded", $certificate);

        return $this->certificates->pdf($certificate)->download("certificate-{$certificate->code}.pdf");
    }

    public function revoke(Certificate $certificate): Certificate
    {
        $certificate->update([
            'status' => Certificate::STATUS_REVOKED,
            'revoked_at' => now(),
        ]);
        $this->logger->log('certificate.revoked', "Certificate {$certificate->code} revoked", $certificate);

        return $certificate->load('issuer:id,name');
    }

    public function destroy(Certificate $certificate): JsonResponse
    {
        $this->logger->log('certificate.deleted', "Certificate {$certificate->code} deleted", $certificate);
        $certificate->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
