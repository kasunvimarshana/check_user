<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Hash;
use DB;
use Carbon\Carbon;
use \Exception;
use Illuminate\Support\Facades\Storage;

use Mail;
use App\Jobs\SendCheckUserEmailJob;

use App\Imports\UserHCMImport;
use App\Imports\UserADImport;
use Excel;

class CheckUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkuser:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for check users (AD and HCM)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        try{
            $host = gethostbyaddr("10.150.152.14");
            $file_uri_user_hcm = "//" . $host . "/userdata$/Common_Share/ICT/Employee_Reconciliation/HCM.xls";
            $file_uri_user_ad = "//" . $host . "/userdata$/Common_Share/ICT/Employee_Reconciliation/BLI-Users.csv";

            //$array = Excel::toArray(new Import, $file);
            //$array = Excel::toCollection(new Import, $file);

            $userHCMImportObject = new UserHCMImport();
            $userADImportObject = new UserADImport();

            $array_user_hcm = Excel::toCollection($userHCMImportObject, $file_uri_user_hcm);
            $array_user_ad = Excel::toCollection($userADImportObject, $file_uri_user_ad);

            $array_user_hcm = $array_user_hcm->last();
            $array_user_ad = $array_user_ad->last();

            foreach($array_user_ad as $key_user_ad => &$value_user_ad){
                if($key_user_ad == 0){
                    unset($array_user_ad[$key_user_ad]);
                    continue;
                }
                if((is_null($value_user_ad[4]))){
                    unset($array_user_ad[$key_user_ad]);
                    continue;
                }
                if((is_null($value_user_ad[3]))){
                    unset($array_user_ad[$key_user_ad]);
                    continue;
                }
                if((strcasecmp($value_user_ad[3] ,'Executive') == 0)){
                    unset($array_user_ad[$key_user_ad]);
                    continue;
                }
                foreach($array_user_hcm as $key_user_hcm => $value_user_hcm){
                    if($key_user_hcm == 0){
                        continue;
                    }
                    if( (intval($value_user_hcm[1]) == intval($value_user_ad[4])) ){
                        //equal
                        unset($array_user_ad[$key_user_ad]);
                        break(1);
                    }
                }
            }

            if( ($array_user_ad) && (!$array_user_ad->isEmpty()) ){
                $emailJob = (new SendCheckUserEmailJob($array_user_ad));
                dispatch($emailJob);
            }
        }catch(Exception $e){
            
        }
        
    }
}
