<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\AttendanceImport;
use Maatwebsite\Excel\Facades\Excel;

class MarkController extends Controller
{
    public function test() {
        $filePath = 'c:/attendance/Посещения.xlsx';

        if (!file_exists($filePath)) {
            return response()->json(['error' => "Файл не найден по пути $filePath"], 404);
        }

        $import = new AttendanceImport();

        Excel::import($import, $filePath);

        return response()->json([
            'message' => 'ok',
            'data' => $import->data,
        ]);

    }
}
