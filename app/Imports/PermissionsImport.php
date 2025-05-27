<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class PermissionsImport extends ValueBinder implements WithHeadingRow, WithCustomValueBinder
{
    public function headingRow(): int
    {
        return 1; 
    }
}