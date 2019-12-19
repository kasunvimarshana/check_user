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
        ini_set('memory_limit', '500M');
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
            $this->checkUser_BLI();
            $this->checkUser_BIA();
            $this->checkUser_BEL();
            $this->checkUser_BCW();
        }catch(Exception $e){
            //dd( $e );
        }
    }
    
    private function checkUser_BLI(){
        //
        $index_1_column_dn = 0;
        $index_1_column_given_name = 1;
        $index_1_column_mail = 3;
        $index_1_column_employee_number = 4;
        $index_1_column_employee_type = 2;

        $index_2_column_employee_number = 0;
        $index_2_column_e_p_f_number = 1;
        $index_2_column_e_m_p_barcode = 2;
        $index_2_column_e_m_p_full_name = 3;
        $index_2_column_e_m_p_calling_name = 4;
        $index_2_designation = 5;
        $index_2_cluster = 6;
        $index_2_location = 7;
        $index_2_department = 8;
        $index_2_roster = 9;
        $index_2_skill_grade = 10;
        $index_2_direct_indirect_status = 11;
        $index_2_supervisor_name = 12;
        
        $emailJob = null;
        $resultDataArray = array();
        $checkUserDataArray = array(
            array(
                'sbu' => 'BLI',
                'host' => '10.227.241.29',
                'file_uri_user_hcm' => '/FCA_UserReconsiliation/BLI.xls',
                'file_uri_user_ad' => '/FCA_UserReconsiliation/BLI_Users.csv',
                'mail_user_array_to' => array(
                    'kasunv@brandix.com', 'Prabhathdh@brandix.com', 'SumithK@brandix.com'
                ),
                'mail_user_array_1' => array(
                    'kasunv@brandix.com', 'Prabhathdh@brandix.com', 'SumithK@brandix.com'
                )
            )
        );

        $resultDataArray['column_number'] = array(
            $index_1_column_dn,
            $index_1_column_given_name,
            $index_1_column_mail,
            $index_1_column_employee_number,
            $index_1_column_employee_type,
        );
        
        foreach( $checkUserDataArray as $checkUserDataKey => $checkUserDataValue ){

            try{

                $sbu = $checkUserDataValue['sbu'];
                $host = gethostbyaddr( $checkUserDataValue['host'] );
                $file_uri_user_hcm = "//" . $host . $checkUserDataValue['file_uri_user_hcm'];
                $file_uri_user_ad = "//" . $host . $checkUserDataValue['file_uri_user_ad'];

                $resultDataArray['check_user_data'] = $checkUserDataValue;

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
                    
                    $array_user_hcm = Excel::toArray([], $file_uri_user_hcm);
                    $array_user_ad = Excel::toArray([], $file_uri_user_ad);
                    $array_user_hcm = array_pop(($array_user_hcm));
                    $array_user_ad = array_pop(($array_user_ad));
                    
                    foreach($array_user_ad as $key_user_ad => &$value_user_ad){
                        if($key_user_ad == 0){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((is_null($value_user_ad[$index_1_column_employee_number]))){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((is_null($value_user_ad[$index_1_column_employee_type]))){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((strcasecmp($value_user_ad[$index_1_column_employee_type] ,'Executive') == 0)){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        foreach($array_user_hcm as $key_user_hcm => $value_user_hcm){
                            if($key_user_hcm == 0){
                                continue;
                            }
                            if( ( (intval($value_user_hcm[$index_2_column_employee_number]) == intval($value_user_ad[$index_1_column_employee_number])) || (intval($value_user_hcm[$index_2_column_e_p_f_number]) == intval($value_user_ad[$index_1_column_employee_number])) ) ){
                                //equal
                                unset($array_user_ad[$key_user_ad]);
                                break(1);
                            }
                        }
                    }

                    if( ($array_user_ad) && (!empty($array_user_ad)) ){
                        $resultDataArray['message_type'] = 'default';
                        $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . ']';
                        $resultDataArray['message_body'] = 'Dear all, IT Clearance pending for following inactive employee(s). Please action';
                        $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_to'];
                        $resultDataArray['array_user_ad'] = $array_user_ad;
                        $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                        //dispatch($emailJob);
                    }else{
                        $resultDataArray['message_type'] = 'default';
                        $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . '] – [Comply]';
                        $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_1'];
                        $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                        //dispatch($emailJob);
                    }

                    unset($array_user_hcm);
                    unset($array_user_ad);
                }else{
                    $resultDataArray['message_type'] = 'error';
                    $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . '] – [Invalid CSV]';
                    $resultDataArray['message_body'] = '';
                    $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_1'];
                    $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                    //dispatch($emailJob);
                }

                dispatch($emailJob);
                unset($resultDataArray);
                unset($checkUserDataValue);
                unset($checkUserDataArray);
            }catch(Exception $e){
                //dd($e);
            }

        }
        
    }
    private function checkUser_BIA(){
        //
        $index_1_column_dn = 0;
        $index_1_column_given_name = 1;
        $index_1_column_mail = 4;
        $index_1_column_employee_number = 2;
        $index_1_column_employee_type = 3;

        $index_2_column_employee_number = 0;
        $index_2_column_e_p_f_number = 1;
        $index_2_column_e_m_p_barcode = 2;
        $index_2_column_e_m_p_full_name = 3;
        $index_2_column_e_m_p_calling_name = 4;
        $index_2_designation = 5;
        $index_2_cluster = 6;
        $index_2_location = 7;
        $index_2_department = 8;
        $index_2_roster = 9;
        $index_2_skill_grade = 10;
        $index_2_direct_indirect_status = 11;
        $index_2_supervisor_name = 12;
        
        $emailJob = null;
        $resultDataArray = array();
        $checkUserDataArray = array(
            array(
                'sbu' => 'BIA',
                'host' => '10.227.241.29',
                'file_uri_user_hcm' => '/FCA_UserReconsiliation/BIA.xls',
                'file_uri_user_ad' => '/FCA_UserReconsiliation/BIA_Users.csv',
                'mail_user_array_to' => array(
                    'kasunv@brandix.com', 'Prabhathdh@brandix.com', 'SumithK@brandix.com', 'KalanaDa@brandix.com'
                ),
                'mail_user_array_1' => array(
                    'kasunv@brandix.com', 'Prabhathdh@brandix.com', 'SumithK@brandix.com', 'KalanaDa@brandix.com'
                )
            )
        );

        $resultDataArray['column_number'] = array(
            $index_1_column_dn,
            $index_1_column_given_name,
            $index_1_column_mail,
            $index_1_column_employee_number,
            $index_1_column_employee_type,
        );
        
        foreach( $checkUserDataArray as $checkUserDataKey => $checkUserDataValue ){

            try{

                $sbu = $checkUserDataValue['sbu'];
                $host = gethostbyaddr( $checkUserDataValue['host'] );
                $file_uri_user_hcm = "//" . $host . $checkUserDataValue['file_uri_user_hcm'];
                $file_uri_user_ad = "//" . $host . $checkUserDataValue['file_uri_user_ad'];

                $resultDataArray['check_user_data'] = $checkUserDataValue;

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
                    
                    $array_user_hcm = Excel::toArray([], $file_uri_user_hcm);
                    $array_user_ad = Excel::toArray([], $file_uri_user_ad);
                    $array_user_hcm = array_pop(($array_user_hcm));
                    $array_user_ad = array_pop(($array_user_ad));
                    
                    foreach($array_user_ad as $key_user_ad => &$value_user_ad){
                        if($key_user_ad == 0){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((is_null($value_user_ad[$index_1_column_employee_number]))){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((is_null($value_user_ad[$index_1_column_employee_type]))){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((strcasecmp($value_user_ad[$index_1_column_employee_type] ,'Executive') == 0)){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        foreach($array_user_hcm as $key_user_hcm => $value_user_hcm){
                            if($key_user_hcm == 0){
                                continue;
                            }
                            if( ( (intval($value_user_hcm[$index_2_column_employee_number]) == intval($value_user_ad[$index_1_column_employee_number])) || (intval($value_user_hcm[$index_2_column_e_p_f_number]) == intval($value_user_ad[$index_1_column_employee_number])) ) ){
                                //equal
                                unset($array_user_ad[$key_user_ad]);
                                break(1);
                            }
                        }
                    }

                    if( ($array_user_ad) && (!empty($array_user_ad)) ){
                        $resultDataArray['message_type'] = 'default';
                        $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . ']';
                        $resultDataArray['message_body'] = 'Dear all, IT Clearance pending for following inactive employee(s). Please action';
                        $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_to'];
                        $resultDataArray['array_user_ad'] = $array_user_ad;
                        $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                        //dispatch($emailJob);
                    }else{
                        $resultDataArray['message_type'] = 'default';
                        $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . '] – [Comply]';
                        $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_1'];
                        $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                        //dispatch($emailJob);
                    }

                    unset($array_user_hcm);
                    unset($array_user_ad);
                }else{
                    $resultDataArray['message_type'] = 'error';
                    $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . '] – [Invalid CSV]';
                    $resultDataArray['message_body'] = '';
                    $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_1'];
                    $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                    //dispatch($emailJob);
                }

                dispatch($emailJob);
                unset($resultDataArray);
                unset($checkUserDataValue);
                unset($checkUserDataArray);
            }catch(Exception $e){
                //dd($e);
            }

        }
        
    }
    private function checkUser_BEL(){
        //
        $index_1_column_dn = 0;
        $index_1_column_given_name = 1;
        $index_1_column_mail = 2;
        $index_1_column_employee_number = 3;
        $index_1_column_employee_type = 4;

        $index_2_column_employee_number = 0;
        $index_2_column_e_p_f_number = 1;
        $index_2_column_e_m_p_barcode = 2;
        $index_2_column_e_m_p_full_name = 3;
        $index_2_column_e_m_p_calling_name = 4;
        $index_2_designation = 5;
        $index_2_cluster = 6;
        $index_2_location = 7;
        $index_2_department = 8;
        $index_2_roster = 9;
        $index_2_skill_grade = 10;
        $index_2_direct_indirect_status = 11;
        $index_2_supervisor_name = 12;
        
        $emailJob = null;
        $resultDataArray = array();
        $checkUserDataArray = array(
            array(
                'sbu' => 'BEL',
                'host' => '10.227.241.29',
                'file_uri_user_hcm' => '/FCA_UserReconsiliation/BEL.xls',
                'file_uri_user_ad' => '/FCA_UserReconsiliation/BEL_Users.csv',
                'mail_user_array_to' => array(
                    'kasunv@brandix.com', 'Prabhathdh@brandix.com', 'SumithK@brandix.com', 'Gihand@brandix.com'
                ),
                'mail_user_array_1' => array(
                    'kasunv@brandix.com', 'Prabhathdh@brandix.com', 'SumithK@brandix.com', 'Gihand@brandix.com'
                )
            )
        );

        $resultDataArray['column_number'] = array(
            $index_1_column_dn,
            $index_1_column_given_name,
            $index_1_column_mail,
            $index_1_column_employee_number,
            $index_1_column_employee_type,
        );
        
        foreach( $checkUserDataArray as $checkUserDataKey => $checkUserDataValue ){

            try{

                $sbu = $checkUserDataValue['sbu'];
                $host = gethostbyaddr( $checkUserDataValue['host'] );
                $file_uri_user_hcm = "//" . $host . $checkUserDataValue['file_uri_user_hcm'];
                $file_uri_user_ad = "//" . $host . $checkUserDataValue['file_uri_user_ad'];

                $resultDataArray['check_user_data'] = $checkUserDataValue;

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
                    
                    $array_user_hcm = Excel::toArray([], $file_uri_user_hcm);
                    $array_user_ad = Excel::toArray([], $file_uri_user_ad);
                    $array_user_hcm = array_pop(($array_user_hcm));
                    $array_user_ad = array_pop(($array_user_ad));
                    
                    foreach($array_user_ad as $key_user_ad => &$value_user_ad){
                        if($key_user_ad == 0){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((is_null($value_user_ad[$index_1_column_employee_number]))){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((is_null($value_user_ad[$index_1_column_employee_type]))){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((strcasecmp($value_user_ad[$index_1_column_employee_type] ,'Executive') == 0)){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        foreach($array_user_hcm as $key_user_hcm => $value_user_hcm){
                            if($key_user_hcm == 0){
                                continue;
                            }
                            if( ( (intval($value_user_hcm[$index_2_column_employee_number]) == intval($value_user_ad[$index_1_column_employee_number])) || (intval($value_user_hcm[$index_2_column_e_p_f_number]) == intval($value_user_ad[$index_1_column_employee_number])) ) ){
                                //equal
                                unset($array_user_ad[$key_user_ad]);
                                break(1);
                            }
                        }
                    }

                    if( ($array_user_ad) && (!empty($array_user_ad)) ){
                        $resultDataArray['message_type'] = 'default';
                        $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . ']';
                        $resultDataArray['message_body'] = 'Dear all, IT Clearance pending for following inactive employee(s). Please action';
                        $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_to'];
                        $resultDataArray['array_user_ad'] = $array_user_ad;
                        $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                        //dispatch($emailJob);
                    }else{
                        $resultDataArray['message_type'] = 'default';
                        $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . '] – [Comply]';
                        $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_1'];
                        $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                        //dispatch($emailJob);
                    }

                    unset($array_user_hcm);
                    unset($array_user_ad);
                }else{
                    $resultDataArray['message_type'] = 'error';
                    $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . '] – [Invalid CSV]';
                    $resultDataArray['message_body'] = '';
                    $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_1'];
                    $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                    //dispatch($emailJob);
                }

                dispatch($emailJob);
                unset($resultDataArray);
                unset($checkUserDataValue);
                unset($checkUserDataArray);
            }catch(Exception $e){
                //dd($e);
            }

        }
    }
    private function checkUser_BCW(){
        //
        $index_1_column_dn = 0;
        $index_1_column_given_name = 1;
        $index_1_column_mail = 2;
        $index_1_column_employee_number = 3;
        $index_1_column_employee_type = 4;

        $index_2_column_employee_number = 0;
        $index_2_column_e_p_f_number = 1;
        $index_2_column_e_m_p_barcode = 2;
        $index_2_column_e_m_p_full_name = 3;
        $index_2_column_e_m_p_calling_name = 4;
        $index_2_designation = 5;
        $index_2_cluster = 6;
        $index_2_location = 7;
        $index_2_department = 8;
        $index_2_roster = 9;
        $index_2_skill_grade = 10;
        $index_2_direct_indirect_status = 11;
        $index_2_supervisor_name = 12;
        
        $emailJob = null;
        $resultDataArray = array();
        $checkUserDataArray = array(
            array(
                'sbu' => 'BCW',
                'host' => '10.227.241.29',
                'file_uri_user_hcm' => '/FCA_UserReconsiliation/BCW.xls',
                'file_uri_user_ad' => '/FCA_UserReconsiliation/BCW_Users.csv',
                'mail_user_array_to' => array(
                    'kasunv@brandix.com', 'Prabhathdh@brandix.com', 'SumithK@brandix.com', 'SacthieesR@brandix.com'
                ),
                'mail_user_array_1' => array(
                    'kasunv@brandix.com', 'Prabhathdh@brandix.com', 'SumithK@brandix.com', 'SacthieesR@brandix.com'
                )
            )
        );

        $resultDataArray['column_number'] = array(
            $index_1_column_dn,
            $index_1_column_given_name,
            $index_1_column_mail,
            $index_1_column_employee_number,
            $index_1_column_employee_type,
        );
        
        foreach( $checkUserDataArray as $checkUserDataKey => $checkUserDataValue ){

            try{

                $sbu = $checkUserDataValue['sbu'];
                $host = gethostbyaddr( $checkUserDataValue['host'] );
                $file_uri_user_hcm = "//" . $host . $checkUserDataValue['file_uri_user_hcm'];
                $file_uri_user_ad = "//" . $host . $checkUserDataValue['file_uri_user_ad'];

                $resultDataArray['check_user_data'] = $checkUserDataValue;

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
                    
                    $array_user_hcm = Excel::toArray([], $file_uri_user_hcm);
                    $array_user_ad = Excel::toArray([], $file_uri_user_ad);
                    $array_user_hcm = array_pop(($array_user_hcm));
                    $array_user_ad = array_pop(($array_user_ad));
                    
                    foreach($array_user_ad as $key_user_ad => &$value_user_ad){
                        if($key_user_ad == 0){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((is_null($value_user_ad[$index_1_column_employee_number]))){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((is_null($value_user_ad[$index_1_column_employee_type]))){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        if((strcasecmp($value_user_ad[$index_1_column_employee_type] ,'Executive') == 0)){
                            unset($array_user_ad[$key_user_ad]);
                            continue;
                        }
                        foreach($array_user_hcm as $key_user_hcm => $value_user_hcm){
                            if($key_user_hcm == 0){
                                continue;
                            }
                            if( ( (intval($value_user_hcm[$index_2_column_employee_number]) == intval($value_user_ad[$index_1_column_employee_number])) || (intval($value_user_hcm[$index_2_column_e_p_f_number]) == intval($value_user_ad[$index_1_column_employee_number])) ) ){
                                //equal
                                unset($array_user_ad[$key_user_ad]);
                                break(1);
                            }
                        }
                    }

                    if( ($array_user_ad) && (!empty($array_user_ad)) ){
                        $resultDataArray['message_type'] = 'default';
                        $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . ']';
                        $resultDataArray['message_body'] = 'Dear all, IT Clearance pending for following inactive employee(s). Please action';
                        $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_to'];
                        $resultDataArray['array_user_ad'] = $array_user_ad;
                        $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                        //dispatch($emailJob);
                    }else{
                        $resultDataArray['message_type'] = 'default';
                        $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . '] – [Comply]';
                        $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_1'];
                        $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                        //dispatch($emailJob);
                    }

                    unset($array_user_hcm);
                    unset($array_user_ad);
                }else{
                    $resultDataArray['message_type'] = 'error';
                    $resultDataArray['message_title'] = ' [ '. $sbu . ' ] ' . 'User Account Reconciliation Report as at [' . $resultDataArray['date_today']->format('Y-m-d') . '] – [Invalid CSV]';
                    $resultDataArray['message_body'] = '';
                    $resultDataArray['check_user_data']['mail_user_array_to'] = $resultDataArray['check_user_data']['mail_user_array_1'];
                    $emailJob = (new SendCheckUserEmailJob( $resultDataArray ));
                    //dispatch($emailJob);
                }

                dispatch($emailJob);
                unset($resultDataArray);
                unset($checkUserDataValue);
                unset($checkUserDataArray);
            }catch(Exception $e){
                //dd($e);
            }

        }
    }
}
