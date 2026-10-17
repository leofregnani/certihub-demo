<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class ParticipantController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return Participant::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(fn ($q) => $q->whereLike('name', $term)
                    ->orWhereLike('email', $term)
                    ->orWhereLike('document', $term));
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function store(Request $request): JsonResponse
    {
        $participant = Participant::create($this->validated($request));
        $this->logger->log('participant.created', "Participant {$participant->name} created", $participant);

        return response()->json($participant, 201);
    }

    public function show(Participant $participant): Participant
    {
        return $participant;
    }

    public function update(Request $request, Participant $participant): Participant
    {
        $participant->update($this->validated($request, $participant));
        $this->logger->log(
            'participant.updated',
            "Participant {$participant->name} updated",
            $participant,
            ['changes' => Arr::except($participant->getChanges(), ['updated_at'])],
        );

        return $participant;
    }

    public function destroy(Participant $participant): JsonResponse
    {
        $this->logger->log('participant.deleted', "Participant {$participant->name} deleted", $participant);
        $participant->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    private function validated(Request $request, ?Participant $participant = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('participants', 'email')->ignore($participant)],
            'document' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
