<?php
    
    require (__DIR__).'/src/customer_rfm.php';
    
    use customer_frm\customer_rfm;
    
    $fake_data = json_decode(file_get_contents((__DIR__).'/test-2.json'),true);
    
    $customer = new customer_rfm($fake_data);
    $customer->priority = 'RFM';
    
    //$recency_calc = $customer->frequency_calc();
    $recency_calc = $customer->customer_group();
    
    print_r($recency_calc);
    //print_r($fake_data);
    
    /*
    foreach($customer->buy_list as $customer_id => $c_detail){
        if(!isset($customer->c_cont[$customer_id])){
            echo $customer->buy_list[$customer_id];
        }
    }
    */