<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class ValueBinder extends DefaultValueBinder
{
    public function bindValue(Cell $cell, $value = null)
    {
        if (str_starts_with($cell->getCoordinate(), 'E') && is_numeric($value)) {
            $date = Date::excelToDateTimeObject($value);
            $cell->setValue($date->format('Y-m-d'));
            return true;
        }
        return parent::bindValue($cell, $value);
    }
}