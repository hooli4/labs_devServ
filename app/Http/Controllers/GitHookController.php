<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitHookController extends Controller
{
    private static $isUpdating = false;
    public function handle(Request $request) {
        $secretKey = $request->input('secret_key');
        $envSecretKey = env('WEBHOOK_SECRET_KEY');

        if ($secretKey !== $envSecretKey) {
            return response()->json(['message' => 'Invalid secret key.'], 403);
        }

        if (self::$isUpdating) {
            return response()->json(['message' => 'Update is already in progress.'], 423);
        }

        // Логируем информацию о запросе
        Log::info('Git hook called', [
            'date' => now(),
            'ip_address' => $request->ip(),
        ]);

        try {
            self::$isUpdating = true;

            $this->executeGitCommands();

            return response()->json(['message' => 'Project updated successfully.']);
        } catch (\Exception $e) {
            Log::error('Error during Git update', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred during the update.'], 500);
        } finally {
            self::$isUpdating = false;
        }
    }

    private function executeGitCommands() {
        $output = [];
        exec('cd ../../main && git reset --hard && git pull origin main', $output);
        Log::info('Switched to main branch', ['output' => $output]);        
    }
}
