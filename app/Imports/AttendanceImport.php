<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class AttendanceImport implements ToCollection, WithChunkReading
{
    public $data = [];
    public $group = null;
    public $count = 0;

    public $count_for_mark = null;


    public function collection(Collection $rows)
    {
        
        foreach ($rows as $row) {
            if (isset($row[0]) && substr_count($row[0], ' ') == 2 && $row[0] != "Оценки за экзамен") {

                $person_data = [];
                $subgroup = null;
                $count_attending = 0;
                $count_laboratories = 0;
                $count_tests = 0;
                $count_fires = 0;
                $count_birds = 0;
                $count_squares = 0;
                $count_single_developer = 0;
                $count_hands = 0;

                $person_data['FIO'] = $row[0];

                if (isset($row[2])) $subgroup = $row[2];

                $person_data['group'] = $this->group;
                $person_data['subgroup'] = $subgroup;

                $start = 26;
                $end = 100;

                for ($i = $start; $i < $end; $i++) {
                    if (isset($row[$i])) {
                        if (str_contains($row[$i], '+')) {
                        $count_attending += 1;
                        }

                        if (str_contains($row[$i], '✅')) {
                            $count_laboratories += substr_count($row[$i], '✅');
                        }

                        if (str_contains($row[$i], '🔥')) {
                            $count_fires += 1;
                        }

                        if (str_contains($row[$i], '🥲')) {
                            $count_squares += 1;
                        }

                        if (str_contains($row[$i], '🐤')) {
                            $count_birds += 1;
                        }

                        if (str_contains($row[$i], 'T')) {
                            $count_tests += 1;
                        }

                        if (str_contains($row[$i], 'g')) {
                            $count_single_developer += 1;
                        }

                        if (str_contains($row[$i], '👌')) {
                            $count_hands += 1;
                        }
                        
                    }
                }

                if (isset($row[22])) {
                    $this->count_for_mark = $row[22] + $count_hands / 2;
                }

                $person_data['attending'] = $count_attending;
                $person_data['laboratory_works'] = $count_laboratories;
                $person_data['count_for_five'] = $this->count_for_mark;

                $mark = 2;
                
                $points = $count_laboratories + $count_birds / 20 + $count_squares / 2 + $count_fires * 1.5 - $count_single_developer + $count_tests * 0.3;

                if ($points >= $this->count_for_mark - 2) $mark = 3;

                if ($points >= $this->count_for_mark - 1) $mark = 4;
                
                if ($points >= $this->count_for_mark) $mark = 5;

                $person_data['points'] = $points;
                $person_data['mark'] = $mark;

                $this->data["$this->count"] = $person_data;
                
                $this->count += 1;
            }
            elseif (isset($row[0]) && $this->group != $row[0]) {
                $this->group = $row[0];
            }
        }
        
    }

    public function chunkSize(): int
    {
        return 10;
    }
}