<?php
    
    require 'customer_rfm.php';
    
    use consumer_rfm\customer_rfm;
    
    
    $fake_data = [
        [
            'id' => 1,
            'customer_name' => 'Hasan Yüksektepe',
            'customer_detail' => [
                'buy_list' => [
                    [
                        'date' => '14.01.2020',
                        'price' => 1000
                    ],
                    [
                        'date' => '13.02.2020',
                        'price' => 1000000
                    ],
                    [
                        'date' => '13.01.2020',
                        'price' => 1000
                    ],
                ]
            ],
        ],
        [
            'id' => 2,
            'customer_name' => 'Murat Yüksektepe',
            'customer_detail' => [
                'buy_list' => [
                    [
                        'date' => '20.01.2020',
                        'price' => 80
                    ],
                ]
            ],
        ],
        [
            'id' => 3,
            'customer_name' => 'Murat Yüksektepe',
            'customer_detail' => [
                'buy_list' => [
                    [
                        'date' => '01.12.2020',
                        'price' => 8000
                    ],
                    [
                        'date' => '01.01.2020',
                        'price' => 80
                    ],
                ]
            ],
        ],
        [
            'id' => 5,
            'customer_name' => 'Murat Yüksektepe',
            'customer_detail' => [
                'buy_list' => [
                    [
                        'date' => '01.12.2020',
                        'price' => 80
                    ],
                    [
                        'date' => '01.01.2020',
                        'price' => 80
                    ],
                    [
                        'date' => '01.02.2020',
                        'price' => 80
                    ],
                ]
            ],
        ],
        [
            'id' => 6,
            'customer_name' => 'Murat Yüksektepe',
            'customer_detail' => [
                'buy_list' => [
                    [
                        'date' => '01.12.2020',
                        'price' => 800
                    ],
                    [
                        'date' => '01.01.2020',
                        'price' => 80
                    ],
                    [
                        'date' => '01.02.2020',
                        'price' => 80
                    ],
                ]
            ],
        ]
    ];
    
    $customer = new customer_rfm($fake_data);
    $customer->priority = 'MRF';
    
    $recency_calc = $customer->score_calc();
    
    print_r($recency_calc);
    

    /*
    $sayilar = [
        250
    ];
    
    $say = array_sum($sayilar);
    print_r(($say / count($sayilar)) * 0.20);
    */