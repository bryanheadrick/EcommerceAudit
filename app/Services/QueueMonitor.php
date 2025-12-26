<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class QueueMonitor
{
    public function getPendingJobsCount(?string $queue = null): int
    {
        $query = DB::table('jobs');

        if ($queue) {
            $query->where('queue', $queue);
        }

        return $query->count();
    }

    public function getFailedJobsCount(?string $queue = null): int
    {
        $query = DB::table('failed_jobs');

        if ($queue) {
            $query->where('queue', $queue);
        }

        return $query->count();
    }

    public function getRecentFailedJobs(int $limit = 5): array
    {
        return DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'exception' => $this->extractExceptionMessage($job->exception),
                    'failed_at' => $job->failed_at,
                ];
            })
            ->toArray();
    }

    public function getAuditJobStatus(string $auditId): array
    {
        $pendingJobs = DB::table('jobs')
            ->where('payload', 'like', "%{$auditId}%")
            ->count();

        $failedJobs = DB::table('failed_jobs')
            ->where('payload', 'like', "%{$auditId}%")
            ->count();

        return [
            'pending' => $pendingJobs,
            'failed' => $failedJobs,
            'has_active_jobs' => $pendingJobs > 0,
        ];
    }

    public function isQueueWorkerRunning(): bool
    {
        $recentJob = DB::table('jobs')
            ->orderBy('id', 'desc')
            ->first();

        if (! $recentJob) {
            return true;
        }

        $jobAge = now()->timestamp - $recentJob->created_at;

        return $jobAge < 300;
    }

    protected function extractExceptionMessage(string $exception): string
    {
        preg_match('/Exception: (.+?) in/', $exception, $matches);

        return $matches[1] ?? 'Unknown error';
    }

    public function getQueueStats(): array
    {
        return [
            'total_pending' => $this->getPendingJobsCount(),
            'total_failed' => $this->getFailedJobsCount(),
            'worker_running' => $this->isQueueWorkerRunning(),
            'recent_failures' => $this->getRecentFailedJobs(3),
        ];
    }
}
