<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChangeLog;
use App\Models\LogRequest;
use App\Jobs\GenerateReport;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function generateReport()
    {
        $this->logStart();

        $timeInterval = env('REPORT_TIME_INTERVAL', 24); // В часах
        $maxExecutionTime = env('REPORT_MAX_EXECUTION_TIME', 5); // В минутах
        $timeoutBetweenRuns = env('REPORT_TIMEOUT_BETWEEN_RUNS', 5); // В минутах
        $maxRetries = env('REPORT_NUMBER_OF_RETRIES', 3);
        $attempts = 0;

        $startTime = microtime(true);

        while ($attempts < $maxRetries) {
            try {

            $methodsStats = $this->getMethodsStats($timeInterval);
            $entityStats = $this->getEntityStats($timeInterval);
            $userStats = $this->getUserStats($timeInterval);

            $fileName = $this->createReport($methodsStats, $entityStats, $userStats);

            $currentTime = microtime(true);
            $executionTime = $currentTime - $startTime;

            if ($executionTime > $maxExecutionTime * 60) {
                unlink(storage_path('/app/reports/'.$fileName));
                throw new \Exception('Максимальное время выполнения превысило лимит в ' . $maxExecutionTime . ' минут.');
            }

            $this->sendToAdmins($fileName);

            sleep(3);

            unlink(storage_path('/app/reports/'.$fileName));

            $this->logEnd();

            break;
            } catch (\Exception $e) {
                Log::error("Ошибка генерации отчета: " . $e->getMessage());
                $attempts++;
                sleep($timeoutBetweenRuns * 60);
            }
        }

        if ($attempts >= $maxRetries) {
            Log::error('Превышено максимальное количество попыток генерации отчета.');
            throw new \Exception('Ошибка при генерации отчета после ' . $maxRetries . ' попыток.');
        }

        return response()->json(['status' => 'success'], 200);
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

    public function create() {
        $timeInterval = env('REPORT_TIME_INTERVAL', 24); // В часах
        $maxExecutionTime = env('REPORT_MAX_EXECUTION_TIME', 5); // В минутах
        $timeoutBetweenRuns = env('REPORT_TIMEOUT_BETWEEN_RUNS', 5); // В минутах
        $maxRetries = env('REPORT_NUMBER_OF_RETRIES', 3);

        GenerateReport::dispatch($maxExecutionTime * 60, $maxRetries, $timeoutBetweenRuns * 60, $timeInterval);
    }
}