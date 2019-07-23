<?php

namespace App\Imports;

//use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class SecondSheetImport implements ToCollection, SkipsUnknownSheets
{
    public function collection(Collection $rows)
    {
        //
    }
    
    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }
}
