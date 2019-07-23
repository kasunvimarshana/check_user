<?php

namespace App\Imports;

//use Illuminate\Database\Eloquent\Model;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

use App\User;
use App\Imports\FirstSheetImport;
use App\Imports\SecondSheetImport;

class UserHCMImport implements ToModel
{
    //
    //use WithConditionalSheets;
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row)
    {
        return new User([
            'employee_number' => $row[0],
            'epf_number' => $row[1],
            'name' => $row[2],
            'designation' => $row[3],
            'department' => $row[4],
            'roster' => $row[5]
        ]);
    }
    
    /*
    public function sheets(): array
    {
        return [
            new FirstSheetImport()
        ];
    }
    
    public function conditionalSheets(): array
    {
        return [
            0 => new FirstSheetImport(),
            1 => new SecondSheetImport()
        ];
    }
    
    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }
    */
}
