<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

/**
 * Audit trail viewer (admin only, enforced in routes/api.php).
 */
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 20), 1), 100);

        return ActivityLog::query()
            ->with('user:id,name,email')
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', $request->string('action').'%'))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(fn ($q) => $q->whereLike('description', $term)->orWhereLike('ip_address', $term));
            })
            ->latest('id')
            ->paginate($perPage);
    }
}
