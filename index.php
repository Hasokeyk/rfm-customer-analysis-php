<?php
    
    require 'customer_rfm.php';
    
    use consumer_rfm\customer_rfm;
    
    
    $fake_data = json_decode(file_get_contents((__DIR__).'/test.json'),true);
    
    $customer = new customer_rfm($fake_data);
    $customer->priority = 'FFF';
    
    $recency_calc = $customer->score_calc();
    
    print_r($recency_calc);
    print_r($fake_data);

    /*
    $sayilar = [
        250
    ];
    
    $say = array_sum($sayilar);
    print_r(($say / count($sayilar)) * 0.20);
    */