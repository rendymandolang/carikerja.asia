<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupRun;
use App\Services\BackupService;
use App\Services\SystemHealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Throwable;

class AdminSystemController extends Controller
{
    public function index(SystemHealthService $healthService)
    {
        return view('admin.system.index', array_merge($healthService->snapshot(), [
            'backupTypes' => BackupRun::TYPES,
        ]));
    }

    public function runBackup(Request $request, BackupService $backupService)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(BackupRun::TYPES)],
        ]);

        try {
            $backup = $backupService->run($validated['type'], $request->user());

            return redirect()
                ->route('admin.system.index')
                ->with('success', ucfirst($backup->type) . ' backup completed: storage/app/private/' . $backup->path);
        } catch (Throwable $exception) {
            return back()->withErrors([
                'backup' => 'Backup failed: ' . $exception->getMessage(),
            ]);
        }
    }

    public function runQueueOnce()
    {
        try {
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--queue' => 'default',
                '--max-jobs' => 50,
                '--max-time' => 55,
            ]);
            $output = trim(Artisan::output());

            return redirect()
                ->route('admin.system.index')
                ->with('success', $output ?: 'Queue worker completed; no pending jobs found.');
        } catch (Throwable $exception) {
            return back()->withErrors([
                'queue' => 'Queue worker failed: ' . $exception->getMessage(),
            ]);
        }
    }
}
