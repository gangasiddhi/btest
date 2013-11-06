<?php
require_once(dirname(__FILE__) . "/../config/config.inc.php");
require_once(dirname(__FILE__) . "/../init.php");

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');
if(!$cron_user)
	$cron_user = $argv[1];
if(!$cron_pass)
	$cron_pass = $argv[2];
$cron_pass = Tools::encrypt($cron_pass);
$file_path=_PS_ROOT_DIR_.'/cron/date.txt';
//Group->getGroups();
//if($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_)
//{
	global $cookie;
    
   /**
    *Function for date add 
    */
    function days_in_month($month, $year) 
    { 
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    } 

    function DateAdd($cur_start_date, $cur_last_date,$interval='01') {

    /*Adding one month to start Date*/
    $cur_start_date=  explode('-', $cur_start_date);


    $day_start=str_pad(($cur_start_date[2]+$interval), 2, "0", STR_PAD_LEFT);
    $cur_s_day = $day_start;
    $cur_s_month = $cur_start_date[1];
    $cur_s_year = $cur_start_date[0];

    $days_in_month=days_in_month($cur_s_month, $cur_s_year);
    if ($cur_s_day > $days_in_month){
        $cur_s_month = str_pad(($cur_s_month+1), 2, "0", STR_PAD_LEFT);
        $cur_s_day = str_pad(($cur_s_day - 30), 2, "0", STR_PAD_LEFT);
        if ($cur_s_month == 13){
            $cur_s_year = $cur_s_year + 1;
            $cur_s_month ='01';
        }
    }

    $new_start_date=$cur_s_year.'-'.$cur_s_month.'-'.$cur_s_day;

    /*Adding one month to End Date*/

    $cur_last_date=  explode('-', $cur_last_date);
    $day_end=str_pad(($cur_last_date[2]+$interval), 2, "0", STR_PAD_LEFT);
    $cur_end_day = $day_end;
    $cur_end_month = $cur_last_date[1];
    $cur_end_year = $cur_last_date[0];
    $end_date_days_in_month=days_in_month($cur_end_month, $cur_end_year);

    if($cur_end_day>$end_date_days_in_month){
        $cur_end_month=str_pad(($cur_end_month+1), 2, "0", STR_PAD_LEFT);
        $cur_end_day=str_pad(($cur_end_day-30), 2, "0", STR_PAD_LEFT);
        if ($cur_end_month == 13){
            $cur_end_year = $cur_end_year + 1;
            $cur_end_month ='01';
        }
    }
    $new_end_date=$cur_end_year.'-'.$cur_end_month.'-'.$cur_end_day;
    //exit;

    return $new_start_date.'@'.$new_end_date;
    }
    
    $get_contents=file_get_contents($file_path);
    $date_array = json_decode($get_contents, true);

    $start_date=$date_array['start_date'];
    $end_date=$date_array['end_date']; 

    /**
     * Function for formating the JSON data
     */
    function format_json($data){
        $output_index_count = 0;
        $output_indexed = array();
        $output_associative = array();
        
        foreach ($data as $key => $value) {
            if(is_array($value)){
                $output_indexed[] = json_encode($key) . ':' .'['.json_encode($value). ']';
            }else{
                $output_indexed[] = json_encode($key) . ':' . json_encode($value);
            }
        }
        
            return  '{'.implode(',', $output_indexed).'}';  
     }
     
     /**
      * Function for curl execution 
      */
     
     function curlExecution($data_string,$url){
         
         $ch = curl_init($url);  
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_string))); 
                $result = curl_exec($ch);
     }
    
           
	function getCustomersDetail($start_date, $end_date)
	{   
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.`id_customer`, c.`email`, cn.`http_referer`, c.`date_add`
		FROM `'._DB_PREFIX_.'customer` c
        LEFT JOIN `'._DB_PREFIX_.'guest` g ON (c.`id_customer`= g.`id_customer`)
        LEFT JOIN `'._DB_PREFIX_.'connections` cn ON (g.`id_guest`= cn.`id_guest`)
        WHERE c.`deleted`=0 AND c.`date_add`>"'.$start_date.' 00:00:01" AND c.`date_add`< "'.$end_date.' 23:59:59"
		ORDER BY c.`date_add`ASC');
	}
        
    $customer_detail=getCustomersDetail($start_date, $end_date);
    
    /*Following function for referrals*/
    
    function getReferralUser($cust_id){
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT r.`email` as referrer_email
		FROM `'._DB_PREFIX_.'referralprogram` r        
		WHERE r.`id_sponsor`='.$cust_id.'');
    }
    
    /**
     * Following functions for Activation & Revenue
     */
    
    function getBoughtProducts($cust_id) {
		$sql = '
		SELECT sum(o.total_paid_real) as total_purchased_amount FROM `'._DB_PREFIX_.'orders` o		
		WHERE o.valid = 1 AND o.`id_customer` ='.$cust_id.'  group by o.`id_customer`';

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
        return $result;
	}
    
     /**
     *Retention
     *  Please uncomment following code when cron is set to run on daily for to get retaintion data
     */
    
    
    function getCustomerRetaintionData($cust_id){
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
        SELECT count(g.`id_guest`) as cust_visitnb
        FROM `'._DB_PREFIX_.'guest` g
        LEFT JOIN `'._DB_PREFIX_.'connections` cn ON (g.`id_guest`= cn.`id_guest`)
        WHERE g.`id_customer`='.$cust_id.' AND cn.`date_add` IS NOT NULL AND cn.`date_add`!="" AND cn.`date_add` BETWEEN cn.`date_add` AND DATE_SUB(CURRENT_DATE ,INTERVAL 4 WEEK)
        GROUP BY g.`id_guest`');
    }     
    
    
    /**
     *Acpuisition 
     * 
     */
    
    foreach($customer_detail as $key => $cust_value){
        
        $acpuisition_data = array("api_key" => "38q33z83ie90579z37o2e538j926", "data" => array("email"=>$cust_value['email'], "referrer"=>$cust_value['http_referer'], "occurred_at"=>$cust_value['date_add']));
        $acpuisition_data_string = format_json($acpuisition_data); 
        $acpuisition_url='https://piratemetrics.com/api/v1/acquisitions';
        curlExecution($acpuisition_data_string,$acpuisition_url);
        
        /**
        *Referrals
        */
        $referralUser=getReferralUser($cust_value['id_customer']);
        $referrals_data = array("api_key" => "38q33z83ie90579z37o2e538j926", "data" => array("customer_email"=>$cust_value['email'], "referree_email"=>$referralUser[0]['referrer_email'], "occurred_at"=>$cust_value['date_add']));
        $referrals_data_string = format_json($referrals_data); 
        $referrals_url='https://piratemetrics.com/api/v1/referrals';
        curlExecution($referrals_data_string,$referrals_url);
        
        /**
         *Activation 
         */
        $getBoughtProducts=getBoughtProducts($cust_value['id_customer']);
        
        if($getBoughtProducts[0]['total_purchased_amount'] !=''){            
            $activation_data = array("api_key" => "38q33z83ie90579z37o2e538j926", "data" => array("email"=>$cust_value['email'], "occurred_at"=>$cust_value['date_add']));
            $activation_data_string = format_json($activation_data); 
            $activation_url='https://piratemetrics.com/api/v1/activations';
            curlExecution($activation_data_string,$activation_url);            
        }
        
        /**
        * Revenue
        */
        
        $total_amount=(($getBoughtProducts[0]['total_purchased_amount'])*100);
            
        $revenue_data = array("api_key" => "38q33z83ie90579z37o2e538j926", "data" => array("email"=>$cust_value['email'], "amount_in_cents"=>$total_amount, "occurred_at"=>$cust_value['date_add']));
        $revenue_data_string = format_json($revenue_data); 
        $revenue_url='https://piratemetrics.com/api/v1/revenues';
        curlExecution($revenue_data_string,$revenue_url);
        
        /**
         *Retention
         */
        
        $retaintionDetailData=getCustomerRetaintionData($cust_value['id_customer']);
               
        if($retaintionDetailData[0]['cust_visitnb']>=7){
            $retaintion_data = array("api_key" => "38q33z83ie90579z37o2e538j926", "data" => array("email"=>$cust_value['email'], "occurred_at"=>$cust_value['date_add']));
            $retaintion_data_string = format_json($retaintion_data);
            $retaintion_url='https://piratemetrics.com/api/v1/retentions';
            curlExecution($retaintion_data_string,$retaintion_url);
        }
        
    }
        
    $curdate = getdate();
    $cday = str_pad(($curdate['mday']+1), 2, "0", STR_PAD_LEFT);
    $cmonth = str_pad(($curdate['mon']), 2, "0", STR_PAD_LEFT);
    $cyear = $curdate['year'];
    $full_cur_date=$cyear.'-'.$cmonth.'-'.$cday;
    
    if($start_date < $full_cur_date && $end_date < $full_cur_date){

        $get_added_date=DateAdd($start_date,$end_date);
        $explode_date=  explode('@', $get_added_date);

        $file_open = fopen($file_path, "w+") or exit("Unable to open file!");
        $date_array=json_encode(array("start_date"=>$explode_date[0], "end_date"=>$explode_date[1]));
        $fileWrite=  fwrite($file_open, $date_array)or exit("can not write file!");
    }else{
        exit('Specified date reached i.e <br>start date='.$start_date.'<br>end date=='.$end_date);
    }

    fclose($file_open);

//}
//else
//{
//	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
//
//	header("Cache-Control: no-store, no-cache, must-revalidate");
//	header("Cache-Control: post-check=0, pre-check=0", false);
//	header("Pragma: no-cache");
//
//	header("Location: ../");
//	exit;
//}
?>
