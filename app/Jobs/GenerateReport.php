<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use App\Models\ChangeLog;
use App\Models\LogRequest;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class GenerateReport implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $timeout;

    public $tries;

    public $timeInterval;

    public $backoff;

    public function __construct($timeout, $tries, $backoff, $timeInterval)
    {
        $this->backoff = $backoff;
        $this->timeout = $timeout;
        $this->tries = $tries;
        $this->timeInterval = $timeInterval;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $timeInterval = $this->timeInterval;
        $methodsStats = $this->getMethodsStats($timeInterval);
        $entityStats = $this->getEntityStats($timeInterval);
        $userStats = $this->getUserStats($timeInterval);

        $fileName = $this->createReport($methodsStats, $entityStats, $userStats);

        $this->sendToAdmins($fileName);

        sleep(5);

        unlink(storage_path('/app/reports/'.$fileName));

        $this->logEnd();
    }

    private function getMethodsStats($timeInterval)
    {
        return LogRequest::select('NAME_METHOD_CONTROLLER', \DB::raw('count(*) as count'))
                    ->where('TIME_CALL', '>=', now()->subHours($timeInterval))
                    ->groupBy('NAME_METHOD_CONTROLLER')
                    ->get();
    }

    private function getEntityStats($timeInterval)
    {
        return ChangeLog::select('entity_type', \DB::raw('count(*) as count'))
                    ->where('created_at', '>=', now()->subHours($timeInterval))
                    ->groupBy('entity_type')
                    ->get();
    }

    private function getUserStats($timeInterval)
    {
        $getUserLogRequest = DB::table('users')
            ->leftJoin('logsrequests', 'users.id', '=', 'logsrequests.USER_ID') 
            ->select('users.id', 'users.name', DB::raw('COUNT(logsrequests.id) AS request_count')) 
            ->where('TIME_CALL', '>=', now()->subHours($timeInterval))
            ->groupBy('users.id', 'users.name')
            ->get();

        $getUserLogLoginRequest = DB::table('logsrequests')
            ->select(
                DB::raw('COUNT(*) as counts'),
                DB::raw('JSON_EXTRACT(BODY_REQUEST, "$.name") as user_name')
            )
            ->where('NAME_METHOD_CONTROLLER', 'AuthController@UserLogin')
            ->where('CODE_STATUS_RESPONSE', 200)
            ->where('TIME_CALL', '>=', now()->subHours($timeInterval))
            ->groupBy('user_name')
            ->get();

        $users = User::all();

        $permissions_user = [];

        foreach ($users as $user) {
            $permissions = $user->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->unique('id');

            $permissions_user[] = [
                'name' => $user->name, 
                'permissions' => $permissions->count(),
            ];
        }

        return [
           $getUserLogRequest,
           $getUserLogLoginRequest,
           $permissions_user,
        ];
    }

    private function createReport($methodsStats, $entityStats, $userStats)
    {
         $data = [
            'methodsStats' => $methodsStats,
            'entityStats' => $entityStats,
            'userStats' => $userStats,
        ];

        $pdf = Pdf::loadView('reports.report', $data);
        $fileName = 'report_' . now()->format('Y_m_d_H_i_s') . '.pdf';
        
        $pdf->save(storage_path('app/reports/' . $fileName));
        return $fileName;
    }

    private function sendToAdmins($fileName)
    {
    
    }
    private function logStart()
    {
        Log::info('Start generating the report.');
    }

    private function logEnd()
    {
        Log::info('Report generation completed.');
    }
}
