<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Models\Permission;
use App\Exports\PermissionsExport;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Imports\PermissionsImport;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ImportExportController extends Controller
{
    public function exportPermissions()
    {
        return Excel::download(new PermissionsExport, 'permissions.xlsx');
    }

    public function exportUsers()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function importUsers(Request $request) {
        //$file = $request->file('file');
        $file = 'C:/importUsers/users.xlsx';
        $mode = $request->input('mode'); //add_or_update || null
        $errorHandling = $request->input('error_handling'); //stop || skip_all || null

        $results = [];
        $processingMode = $mode;

        $import = new UsersImport();

        $collection = Excel::toCollection($import, $file)->first();

        $errors = [];

        if ($errorHandling == 'stop' || $errorHandling == 'skip_all') {
            try {
                DB::transaction(function() use ($collection, &$errors, $errorHandling, $processingMode, &$results) {
                    foreach ($collection as $index => $row) {
                        $row = $row->toArray();
                        $rowNumber = $index + 2;
                        try {
                            $existing = null;
                            if ($processingMode === 'add_or_update') {
                                $existing = User::where('id', $row['id'])->first();
                            }
                            
                            if ($existing && $processingMode === 'add_or_update') {
                        
                                if ($existing->name == $row['name']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству name";
                                }

                                else $existing->name = $row['name'];

                                if ($existing->email == $row['email']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству email";
                                }

                                else $existing->email = $row['email'];

                                if (Hash::check($row['password'], $existing->password)) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству password";
                                }

                                else $existing->password = $row['password'];

                                if ($existing->birthday == $row['birthday']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству birthday";
                                }
                                
                                else $existing->birthday = $row['birthday'];

                                $results[] = "Запись №{$rowNumber} успешно обновила запись с ID={$existing->id}";
                                $existing->updated_at = Carbon::now();
                                $existing->save();
                            } else {
                                $row['created_at'] = Carbon::now();
                                $row['updated_at'] = Carbon::now();
                                $new = User::create($row);
                                $results[] = "Запись №{$rowNumber} успешно добавлена с ID={$new->id}";
                            }
                        } catch (\Exception $e) {
                            $errors[] = "Ошибка в строке {$rowNumber}: {$e->getMessage()}";
                            if ($errorHandling == 'stop') {
                                throw $e;
                            }
                        }
                    } 

                    if ($errorHandling == 'skip_all' && !empty($errors)) throw new Exception;
                });
            } catch (\Exception $e) {
                if ($errorHandling === 'stop' && !empty($errors)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Обнаружена ошибка, операция остановлена.',
                    'errors' => $errors
                ]);
            }

                if ($errorHandling === 'skip_all' && !empty($errors)) {
                    return response()->json([
                        'status' => 'partial',
                        'message' => 'Обработка завершена с ошибками, без сохранения.',
                        'errors' => $errors,
                        'expected_results' => $results
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Импорт завершен.',
                'results' => $results,
                'errors' => $errors
            ]);  
        }
        else {
            foreach($collection as $index => $row) {
                try {
                    DB::transaction(function () use (&$collection, $index, $row, &$results, &$errors, $processingMode) {
                    $row = $row->toArray();
                        $rowNumber = $index + 2;
                        try {
                            $existing = null;
                            if ($processingMode === 'add_or_update') {
                                $existing = User::where('id', $row['id'])->first();
                            }
                            
                            if ($existing && $processingMode === 'add_or_update') {
                        
                                if ($existing->name == $row['name']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству name";
                                }

                                else $existing->name = $row['name'];

                                if ($existing->email == $row['email']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству email";
                                }

                                else $existing->email = $row['email'];

                                if (Hash::check($row['password'], $existing->password)) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству password";
                                }

                                else $existing->password = $row['password'];

                                if ($existing->birthday == $row['birthday']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству birthday";
                                }
                                
                                else $existing->birthday = $row['birthday'];

                                $results[] = "Запись №{$rowNumber} успешно обновила запись с ID={$existing->id}";
                                $existing->updated_at = Carbon::now();
                                $existing->save();
                            } else {
                                $row['created_at'] = Carbon::now();
                                $row['updated_at'] = Carbon::now();
                                $new = User::create($row);
                                $results[] = "Запись №{$rowNumber} успешно добавлена с ID={$new->id}";
                            }
                        } catch (\Exception $e) {
                            $errors[] = "Ошибка в строке {$rowNumber}: {$e->getMessage()}";
                            throw $e;
                        }
                    });
                } catch (Exception $e) {}
            } 

            return response()->json([
                'status' => 'success',
                'message' => 'Импорт завершен.',
                'results' => $results,
                'errors' => $errors
            ]); 
        }
    }

    public function importPermissions(Request $request) {
        //$file = $request->file('file');
        $file = 'C:/importPermissions/permissions.xlsx';
        $mode = $request->input('mode'); //add_or_update || null
        $errorHandling = $request->input('error_handling'); //stop || skip_all || null

        $results = [];
        $processingMode = $mode;

        $import = new PermissionsImport();

        $collection = Excel::toCollection($import, $file)->first();

        $errors = [];

        if ($errorHandling == 'stop' || $errorHandling == 'skip_all') {
            try {
                DB::transaction(function() use ($collection, &$errors, $errorHandling, $processingMode, &$results) {
                    foreach ($collection as $index => $row) {
                        $row = $row->toArray();
                        $rowNumber = $index + 2;
                        try {
                            $existing = null;
                            if ($processingMode === 'add_or_update') {
                                $existing = Permission::where('id', $row['id'])->first();
                            }
                            
                            if ($existing && $processingMode === 'add_or_update') {
                        
                                if ($existing->name == $row['name']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству name";
                                }

                                else $existing->name = $row['name'];

                                if ($existing->code == $row['code']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству code";
                                }

                                else $existing->code = $row['code'];

                                if ($existing->description == $row['description']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству description";
                                }

                                else $existing->description = $row['description'];

                                $results[] = "Запись №{$rowNumber} успешно обновила запись с ID={$existing->id}";
                                $existing->save();
                            } else {
                                $row['created_at'] = Carbon::now();
                                $row['created_by'] = 0; 
                                $new = Permission::create($row);
                                $results[] = "Запись №{$rowNumber} успешно добавлена с ID={$new->id}";
                            }
                        } catch (\Exception $e) {
                            $errors[] = "Ошибка в строке {$rowNumber}: {$e->getMessage()}";
                            if ($errorHandling == 'stop') {
                                throw $e;
                            }
                        }
                    } 

                    if ($errorHandling == 'skip_all' && !empty($errors)) throw new Exception;
                });
            } catch (\Exception $e) {
                if ($errorHandling === 'stop' && !empty($errors)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Обнаружена ошибка, операция остановлена.',
                    'errors' => $errors
                ]);
            }

                if ($errorHandling === 'skip_all' && !empty($errors)) {
                    return response()->json([
                        'status' => 'partial',
                        'message' => 'Обработка завершена с ошибками, без сохранения.',
                        'errors' => $errors,
                        'expected_results' => $results
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Импорт завершен.',
                'results' => $results,
                'errors' => $errors
            ]);  
        }
        else {
            foreach($collection as $index => $row) {
                try {
                    DB::transaction(function () use (&$collection, $index, $row, &$results, &$errors, $processingMode) {
                    $row = $row->toArray();
                        $rowNumber = $index + 2;
                        try {
                            $existing = null;
                            if ($processingMode === 'add_or_update') {
                                $existing = Permission::where('id', $row['id'])->first();
                            }
                            
                            if ($existing && $processingMode === 'add_or_update') {
                        
                                if ($existing->name == $row['name']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству name";
                                }

                                else $existing->name = $row['name'];

                                if ($existing->code == $row['code']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству code";
                                }

                                else $existing->code = $row['code'];

                                if ($existing->description == $row['description']) {
                                    $results[] = "Запись №{$rowNumber} содержит дубликат записи №{$existing->id} по свойству description";
                                }

                                else $existing->description = $row['description'];

                                $results[] = "Запись №{$rowNumber} успешно обновила запись с ID={$existing->id}";
                                $existing->save();
                            } else {
                                $row['created_at'] = Carbon::now();
                                $row['created_by'] = 0; 
                                $new = Permission::create($row);
                                $results[] = "Запись №{$rowNumber} успешно добавлена с ID={$new->id}";
                            }
                        } catch (\Exception $e) {
                            $errors[] = "Ошибка в строке {$rowNumber}: {$e->getMessage()}";
                            throw $e;
                        }
                    });
                } catch (Exception $e) {}
            } 

            return response()->json([
                'status' => 'success',
                'message' => 'Импорт завершен.',
                'results' => $results,
                'errors' => $errors
            ]); 
        }
    }
}