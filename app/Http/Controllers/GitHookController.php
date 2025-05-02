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
            // Установим флаг выполнения обновления
            self::$isUpdating = true;

            // Выполнение команд Git
            $this->executeGitCommands();

            // Завершение работы
            return response()->json(['message' => 'Project updated successfully.']);
        } catch (\Exception $e) {
            Log::error('Error during Git update', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred during the update.'], 500);
        } finally {
            // Сброс флага обновления
            self::$isUpdating = false;
        }
    }

    private function executeGitCommands() {
        // Переключение на главную ветку (master/main)
        $output = [];
        exec('git checkout main 2>&1', $output);
        Log::info('Switched to main branch', ['output' => implode("\n", $output)]);

        // Отмена всех изменений
        exec('git reset --hard 2>&1', $output);
        Log::info('All changes reverted', ['output' => implode("\n", $output)]);

        // Обновление проекта
        exec('git pull origin main 2>&1', $output);
        Log::info('Project updated', ['output' => implode("\n", $output)]);
    }
}
