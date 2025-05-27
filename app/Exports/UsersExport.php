<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\User;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return User::all(['id', 'name', 'email', 'birthday']);
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'email',
            'birthday',
        ];
    }
}