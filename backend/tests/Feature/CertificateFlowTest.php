<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Participant;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CertificateFlowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_issue_certificate_then_validate_it_publicly(): void
    {
        Sanctum::actingAs($this->admin());

        $participant = Participant::create(['name' => 'Maria Souza']);
        $course = Course::create(['name' => 'Web Development', 'workload_hours' => 40]);

        $response = $this->postJson('/api/certificates', [
            'participant_id' => $participant->id,
            'course_id' => $course->id,
        ])->assertCreated()->assertJsonPath('participant_name', 'Maria Souza');

        $code = $response->json('code');
        $this->assertMatchesRegularExpression('/^CRT-\d{4}-[0-9A-F]{8}$/', $code);

        // No token needed for the public validation endpoint.
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/public/certificates/'.$code)
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('certificate.course_name', 'Web Development')
            ->assertJsonPath('certificate.workload_hours', 40);

        $this->assertDatabaseHas('activity_logs', ['action' => 'certificate.issued']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'certificate.validated']);
    }

    public function test_unknown_code_is_not_valid(): void
    {
        $this->getJson('/api/public/certificates/CRT-2026-DOESNOTEXIST')
            ->assertNotFound()
            ->assertJsonPath('valid', false);
    }

    public function test_revoked_certificate_is_reported_as_invalid(): void
    {
        Sanctum::actingAs($this->admin());

        $participant = Participant::create(['name' => 'João Lima']);
        $course = Course::create(['name' => 'Data Analysis', 'workload_hours' => 20]);
        $id = $this->postJson('/api/certificates', [
            'participant_id' => $participant->id,
            'course_id' => $course->id,
        ])->json('id');

        $code = $this->postJson("/api/certificates/{$id}/revoke")->assertOk()->json('code');

        $this->getJson('/api/public/certificates/'.$code)
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('status', 'revoked');
    }

    public function test_certificate_keeps_its_data_after_the_participant_is_renamed(): void
    {
        Sanctum::actingAs($this->admin());

        $participant = Participant::create(['name' => 'Old Name']);
        $course = Course::create(['name' => 'Leadership Workshop', 'workload_hours' => 8]);
        $code = $this->postJson('/api/certificates', [
            'participant_id' => $participant->id,
            'course_id' => $course->id,
        ])->json('code');

        $this->putJson('/api/participants/'.$participant->id, ['name' => 'New Name'])->assertOk();

        $this->getJson('/api/public/certificates/'.$code)
            ->assertJsonPath('certificate.participant_name', 'Old Name');
    }

    public function test_pdf_download_works_and_is_logged(): void
    {
        Sanctum::actingAs($this->admin());

        $participant = Participant::create(['name' => 'Ana Pereira']);
        $course = Course::create(['name' => 'Project Management', 'workload_hours' => 30]);
        $id = $this->postJson('/api/certificates', [
            'participant_id' => $participant->id,
            'course_id' => $course->id,
        ])->json('id');

        $this->get("/api/certificates/{$id}/pdf")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertDatabaseHas('activity_logs', ['action' => 'certificate.downloaded']);
    }

    public function test_qr_code_is_generated_as_a_png_data_uri(): void
    {
        $uri = app(CertificateService::class)->qrDataUri('http://localhost:5173/validate/CRT-2026-AAAAAAAA');

        $this->assertNotNull($uri, 'QR generation failed (check the gd extension and the qrcode options)');
        $this->assertStringStartsWith('data:image/png;base64,', $uri);
    }
}
