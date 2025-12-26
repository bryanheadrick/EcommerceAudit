<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Services\AuditService;
use App\Services\QueueMonitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private QueueMonitor $queueMonitor
    ) {
    }

    public function index(Request $request): View
    {
        $audits = Audit::with('createdBy')
            ->when($request->user(), function ($query) use ($request) {
                return $query->where('created_by', $request->user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $queueStats = $this->queueMonitor->getQueueStats();

        return view('audits.index', [
            'audits' => $audits,
            'queueStats' => $queueStats,
        ]);
    }

    public function create(): View
    {
        return view('audits.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'max_pages' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $audit = $this->auditService->createAudit(
            url: $validated['url'],
            user: $request->user(),
            maxPages: $validated['max_pages'] ?? null
        );

        $this->auditService->startAudit($audit);

        return redirect()
            ->route('audits.show', $audit)
            ->with('success', 'Audit created and started successfully!');
    }

    public function show(Audit $audit): View
    {
        $this->authorize('view', $audit);

        $audit->load([
            'pages',
            'issues',
            'links',
            'checkoutSteps',
        ]);

        $summary = $this->auditService->getAuditSummary($audit);

        return view('audits.show', [
            'audit' => $audit,
            'summary' => $summary,
        ]);
    }

    public function edit(Audit $audit): View
    {
        $this->authorize('update', $audit);

        if ($audit->isProcessing()) {
            return redirect()
                ->route('audits.show', $audit)
                ->with('error', 'Cannot edit an audit that is currently processing.');
        }

        return view('audits.edit', [
            'audit' => $audit,
        ]);
    }

    public function update(Request $request, Audit $audit): RedirectResponse
    {
        $this->authorize('update', $audit);

        if ($audit->isProcessing()) {
            return redirect()
                ->route('audits.show', $audit)
                ->with('error', 'Cannot update an audit that is currently processing.');
        }

        $validated = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'max_pages' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $audit->update([
            'url' => $validated['url'],
            'max_pages' => $validated['max_pages'] ?? $audit->max_pages,
        ]);

        return redirect()
            ->route('audits.show', $audit)
            ->with('success', 'Audit updated successfully!');
    }

    public function destroy(Audit $audit): RedirectResponse
    {
        $this->authorize('delete', $audit);

        if ($audit->isProcessing()) {
            $this->auditService->cancelAudit($audit);
        }

        $this->auditService->deleteAudit($audit);

        return redirect()
            ->route('audits.index')
            ->with('success', 'Audit deleted successfully!');
    }

    public function restart(Audit $audit): RedirectResponse
    {
        $this->authorize('update', $audit);

        if ($audit->isProcessing()) {
            return redirect()
                ->route('audits.show', $audit)
                ->with('error', 'Audit is already processing.');
        }

        $audit->update([
            'status' => 'pending',
            'score' => null,
            'started_at' => null,
            'completed_at' => null,
            'jobs_total' => 0,
            'jobs_completed' => 0,
            'jobs_failed' => 0,
            'current_step' => null,
            'error_message' => null,
        ]);

        $audit->issues()->delete();
        $audit->links()->delete();
        $audit->checkoutSteps()->delete();

        foreach ($audit->pages as $page) {
            $page->performanceMetrics()->delete();
            $page->delete();
        }

        $this->auditService->startAudit($audit);

        return redirect()
            ->route('audits.show', $audit)
            ->with('success', 'Audit restarted successfully!');
    }

    public function cancel(Audit $audit): RedirectResponse
    {
        $this->authorize('update', $audit);

        if (! $audit->isProcessing()) {
            return redirect()
                ->route('audits.show', $audit)
                ->with('error', 'Audit is not currently processing.');
        }

        $this->auditService->cancelAudit($audit);

        return redirect()
            ->route('audits.show', $audit)
            ->with('success', 'Audit cancelled successfully!');
    }
}
