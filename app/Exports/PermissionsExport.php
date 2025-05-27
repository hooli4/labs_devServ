<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Permission;

class PermissionsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Permission::all(['id', 'name', 'description', 'code']);
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'description',
            'code',
        ];
    }
}