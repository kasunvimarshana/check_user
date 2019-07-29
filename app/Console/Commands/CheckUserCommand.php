<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\File;
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
        $emailJob = null;
        $resultDataArray = array();
        $checkUserDataArray = array(
            array(
                'sbu' => 'BLI',
                'host' => '10.150.152.14',
                'file_uri_user_hcm' => '/userdata$/Common_Share/ICT/Employee_Reconciliation/HCM.xls',
                'file_uri_user_ad' => '/userdata$/Common_Share/ICT/Employee_Reconciliation/BLI-Users.csv',
                'mail_user_array_to' => array(
                    'kasunv@brandix.com', 'Prabhathdh@brandix.com', 'SumithK@brandix.com', 'SamithaSu@brandix.com', 'PoornimalA@brandix.com', 'TharangaWij@brandix.com'
                )
            )
        );

        foreach( $checkUserDataArray as $checkUserDataKey => $checkUserDataValue ){

            try{

                $sbu = $checkUserDataValue['sbu'];
                $host = gethostbyaddr( $checkUserDataValue['host'] );
                $file_uri_user_hcm = "//" . $host . $checkUserDataValue['file_uri_user_hcm'];
                $file_uri_user_ad = "//" . $host . $checkUserDataValue['file_uri_user_ad'];

                $resultDataArray['check_user_data'] = $checkUserDataValue;
                    
                //$array = Excel::toArray(new Import, $file);
                //$array = Excel::toCollection(new Import, $file);

                //$content = File::lastModified( $file_uri );
                //$content = File::isFile( $file_uri );
                //$content = File::extension( $file_uri );
                //$content = File::basename( $file_uri );
                //$content = File::name( $file_uri );
                //$content = File::exists( $file_uri );
                //$content = File::isReadable( $file_uri );

                //$dt1 = Carbon::now()->startOfDay();
                //$dt2 = $dt1->copy()->timestamp( $content );
                //$dt2 = $dt1->copy()->setTimestamp( $content )->startOfDay();
                //$dt1->greaterThan( $dt2 );

                $date_today = Carbon::now()->startOfDay();
                $date_timestamp_last_modified_hcm = $date_today->timestamp; //File::lastModified( $file_uri_user_hcm );
                $date_timestamp_last_modified_ad = $date_today->timestamp; //File::lastModified( $file_uri_user_ad );
                
                if( File::exists( $file_uri_user_hcm ) ){
                    $date_timestamp_last_modified_hcm = File::lastModified( $file_uri_user_hcm );
                }
                
                if( File::exists( $file_uri_user_ad ) ){
                    $date_timestamp_last_modified_ad = File::lastModified( $file_uri_user_ad );
                }

                $date_last_modified_hcm = $date_today->copy()->setTimestamp( $date_timestamp_last_modified_hcm )->startOfDay();
                $date_last_modified_ad = $date_today->copy()->setTimestamp( $date_timestamp_last_modified_ad )->startOfDay();
                
                $resultDataArray['date_today'] = $date_today;
                $resultDataArray['date_last_modified_hcm'] = $date_last_modified_hcm;
                $resultDataArray['date_last_modified_ad'] = $date_last_modified_ad;

                if( ($date_today->lessThanOrEqualTo( $date_last_modified_hcm )) && ($date_today->lessThanOrEqualTo( $date_last_modified_ad )) ){

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
                        $resultDataArray['message_title'] = 'User Account Reconciliation Report';
                        $resultDataArray['message_body'] = 'Dear all, <br/>
                        Please find the user accounts which are active in Active Directory & inactive in HRIS. 
                        Please do the needful in your respective areas.';
                        $resultDataArray['array_user_ad'] = $array_user_ad;
                        $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                        //dispatch($emailJob);
                    }else{
                        $resultDataArray['message_title'] = 'User Account verified, No discrepancy is found';
                        $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                        //dispatch($emailJob);
                    }

                }else{
                    $resultDataArray['message_title'] = 'CSV file update error';
                    $resultDataArray['message_body'] = 'Dear all, <br/>'
                        .'AD Backup Date : '. $resultDataArray['date_last_modified_ad'] .'<br/>' 
                        .'HCM Backup Date : '. $resultDataArray['date_last_modified_hcm'] .'<br/>';
                    $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                    //dispatch($emailJob);
                }

                dispatch($emailJob);
            }catch(Exception $e){

            }

        }
        
    }
}
