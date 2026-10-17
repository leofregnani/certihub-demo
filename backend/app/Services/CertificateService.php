<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Participant;
use App\Models\User;
use Barryvdh\DomPDF\PDF as DomPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Log;

class CertificateService
{
    /**
     * Issues a certificate. Names and workload are copied ("snapshot") so later edits
     * to the participant or course never change a certificate that was already issued.
     */
    public function issue(Participant $participant, Course $course, ?User $issuer = null, ?string $issuedAt = null): Certificate
    {
        return Certificate::create([
            'code' => $this->generateCode(),
            'participant_id' => $participant->id,
            'course_id' => $course->id,
            'issued_by' => $issuer?->id,
            'participant_name' => $participant->name,
            'course_name' => $course->name,
            'workload_hours' => $course->workload_hours,
            'institution_name' => config('certihub.institution.name'),
            'issued_at' => $issuedAt ?? now()->toDateString(),
            'status' => Certificate::STATUS_VALID,
        ]);
    }

    public function generateCode(): string
    {
        do {
            $code = 'CRT-'.now()->format('Y').'-'.strtoupper(bin2hex(random_bytes(4)));
        } while (Certificate::where('code', $code)->exists());

        return $code;
    }

    public function pdf(Certificate $certificate): DomPdf
    {
        return Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
            'qr' => $this->qrDataUri($certificate->validationUrl()),
            'signatory' => config('certihub.institution.signatory'),
            'signatoryRole' => config('certihub.institution.signatory_role'),
        ])->setPaper('a4', 'landscape');
    }

    private function qrDataUri(string $url): ?string
    {
        try {
            $options = new QROptions([
                'outputType' => QROutputInterface::GDIMG_PNG,
                'outputBase64' => true,
                'scale' => 6,
                'quietzoneSize' => 1,
            ]);

            return (new QRCode($options))->render($url);
        } catch (\Throwable $e) {
            // The PDF is still useful without the QR code; the printed code can be validated manually.
            Log::warning('QR code generation failed: '.$e->getMessage());

            return null;
        }
    }
}
