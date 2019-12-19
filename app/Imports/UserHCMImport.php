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
    protected $index_2_column_employee_number = 0;
    protected $index_2_column_e_p_f_number = 1;
    protected $index_2_column_e_m_p_barcode = 2;
    protected $index_2_column_e_m_p_full_name = 3;
    protected $index_2_column_e_m_p_calling_name = 4;
    protected $index_2_designation = 5;
    protected $index_2_cluster = 6;
    protected $index_2_location = 7;
    protected $index_2_department = 8;
    protected $index_2_roster = 9;
    protected $index_2_skill_grade = 10;
    protected $index_2_direct_indirect_status = 11;
    protected $index_2_supervisor_name = 12;
    //use WithConditionalSheets;
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row)
    {
        return new User([
            'employee_number' => $row[$this->index_2_column_employee_number],
            'e_p_f_number' => $row[$this->index_2_column_e_p_f_number],
            'e_m_p_barcode' => $row[$this->index_2_column_e_m_p_barcode],
            'e_m_p_full_name' => $row[$this->index_2_column_e_m_p_full_name],
            'e_m_p_calling_name' => $row[$this->index_2_column_e_m_p_calling_name],
            'designation' => $row[$this->index_2_designation],
            'cluster' => $row[$this->index_2_cluster],
            'location' => $row[$this->index_2_location],
            'department' => $row[$this->index_2_department],
            'roster' => $row[$this->index_2_roster],
            'skill_grade' => $row[$this->index_2_skill_grade],
            'direct_indirect_status' => $row[$this->index_2_direct_indirect_status],
            'supervisor_name' => $row[$this->index_2_supervisor_name]
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
