<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Participant;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Database\Seeder;

/**
 * Demo data only. All names, e-mails and codes are fictional.
 * The demo password is "password" — never use these accounts in production.
 */
class DatabaseSeeder extends Seeder
{
    public function run(CertificateService $certificates): void
    {
        $admin = User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        User::create([
            'name' => 'Staff Demo',
            'email' => 'user@example.com',
            'password' => 'password',
            'role' => User::ROLE_USER,
        ]);

        $participants = collect([
            'Maria Souza', 'João Lima', 'Ana Pereira', 'Carlos Rocha',
            'Fernanda Alves', 'Lucas Martins', 'Beatriz Costa', 'Rafael Gomes',
        ])->map(fn (string $name, int $i) => Participant::create([
            'name' => $name,
            'email' => 'participant'.($i + 1).'@example.com',
            'document' => '000.000.00'.$i.'-00',
            'phone' => '+55 11 90000-000'.$i,
        ]));

        $courses = collect([
            ['Web Development', 40],
            ['Data Analysis', 20],
            ['Project Management', 30],
            ['Leadership Workshop', 8],
        ])->map(fn (array $c) => Course::create([
            'name' => $c[0],
            'workload_hours' => $c[1],
            'description' => 'Demo course used to illustrate the system.',
        ]));

        // A handful of certificates spread over the last months, so the dashboard chart has data.
        foreach ($participants->take(6) as $i => $participant) {
            $certificates->issue(
                $participant,
                $courses[$i % $courses->count()],
                $admin,
                now()->subMonths($i % 4)->subDays($i)->toDateString(),
            );
        }

        Certificate::query()->latest('id')->first()?->update([
            'status' => Certificate::STATUS_REVOKED,
            'revoked_at' => now(),
        ]);
    }
}
