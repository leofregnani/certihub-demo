<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $from = now()->startOfMonth()->subMonths(5);

        $byMonth = Certificate::where('issued_at', '>=', $from->toDateString())
            ->get(['issued_at'])
            ->groupBy(fn ($c) => $c->issued_at->format('Y-m'))
            ->map(fn ($group) => $group->count());

        $months = collect(range(0, 5))->map(function (int $i) use ($from, $byMonth) {
            $month = $from->copy()->addMonths($i);

            return [
                'month' => $month->format('Y-m'),
                'label' => $month->format('M'),
                'total' => $byMonth[$month->format('Y-m')] ?? 0,
            ];
        });

        return response()->json([
            'totals' => [
                'participants' => Participant::count(),
                'courses' => Course::count(),
                'certificates' => Certificate::count(),
                'valid_certificates' => Certificate::where('status', Certificate::STATUS_VALID)->count(),
                'validations' => ActivityLog::where('action', 'certificate.validated')->count(),
            ],
            'per_month' => $months,
            'latest' => Certificate::latest('id')->limit(5)
                ->get(['id', 'code', 'participant_name', 'course_name', 'issued_at', 'status']),
        ]);
    }
}
