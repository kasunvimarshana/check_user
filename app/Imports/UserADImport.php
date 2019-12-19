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

class UserADImport implements ToModel
{
    //
    protected $index_1_column_dn = 0;
    protected $index_1_column_given_name = 1;
    protected $index_1_column_mail = 2;
    protected $index_1_column_employee_number = 3;
    protected $index_1_column_employee_type = 4;
    //use WithConditionalSheets;
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row)
    {
        return new User([
            'employee_number' => $row[$this->index_1_column_employee_number],
            'employee_type' => $row[$this->index_1_column_employee_type],
            'mail' => $row[$this->index_1_column_mail],
            'given_name' => $row[$this->index_1_column_given_name],
            'dn' => $row[$this->index_1_column_dn]
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
