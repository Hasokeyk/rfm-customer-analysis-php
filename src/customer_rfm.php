<?php
    
    
    namespace consumer_rfm;
    
    
    use DateTime;
    
    class customer_rfm{
        
        public $buy_list = null;
        
        private $recency_data   = [];
        private $frequency_data = [];
        private $monetary_data  = [];
        
        public $recency_formula   = [
            5 => 30,
            4 => 45,
            3 => 60,
            2 => 90,
            1 => 99999,
        ];
        public $frequency_formula = [
            5 => 99999,
            4 => 7,
            3 => 5,
            2 => 3,
            1 => 1,
        ];
        public $monetary_formula  = [
            5 => 99999,
            4 => 300,
            3 => 150,
            2 => 100,
            1 => 50,
        ];
        
        public $priority = 'RFM';
        
        public $customer_groups = [
            'best_customer'             => [
                'name' => 'Best Customer',
                'rfm'  => [
                    'r' => [3, 4, 5],
                    'f' => [4, 5],
                    'm' => [4, 5],
                ],
            ],
            'loyal_customer'            => [
                'name' => 'Loyal Customer',
                'rfm'  => [
                    'r' => [3, 4, 5],
                    'f' => [3, 4, 5],
                    'm' => [3, 4, 5],
                ],
            ],
            'new_customer'              => [
                'name' => 'New Customer',
                'rfm'  => [
                    'r' => [4, 5],
                    'f' => [1, 2],
                    'm' => [1, 2],
                ],
            ],
            'promising_customer'        => [
                'name' => 'Promising',
                'rfm'  => [
                    'r' => [3, 4],
                    'f' => [2, 3, 4],
                    'm' => [2, 3, 4],
                ],
            ],
            'warning_customer'          => [
                'name' => 'Customer Needs Attention',
                'rfm'  => [
                    'r' => [2],
                    'f' => [2, 3, 4, 5],
                    'm' => [2, 3, 4, 5],
                ],
            ],
            'about_to_sleep_customer'   => [
                'name' => 'About to Sleep',
                'rfm'  => [
                    'r' => [2, 3],
                    'f' => [1],
                    'm' => [2, 3],
                ],
            ],
            'cannot_lose_them_customer' => [
                'name' => 'Cannot Lose Them',
                'rfm'  => [
                    'r' => [1, 2],
                    'f' => [3, 4, 5],
                    'm' => [3, 4, 5],
                ],
            ],
            'lost_customer'             => [
                'name' => 'Lost Customers',
                'rfm'  => [
                    'r' => [1],
                    'f' => [1],
                    'm' => [1],
                ],
            ],
        ];
        
        public function __construct($buy_list){
            
            $this->buy_list = $buy_list;
            
        }
        
        public function recency_calc($buy_list = null){
            
            $buy_list = $buy_list??$this->buy_list;
            
            $recency_dates = [];
            foreach($buy_list as $customer){
                
                $dates           = [];
                $total_day_count = 0;
                foreach($customer['customer_detail']['buy_list'] as $buy_item){
                    $new_dates                = (str_replace(['/', '.'], '-', $buy_item['date']));
                    $dates[$customer['id']][] = $new_dates;
                    $total_day_count++;
                }
                
                $dates = $this->date_order_by($dates[$customer['id']]);
                
                $tarih1   = new DateTime($dates[0]);
                $tarih2   = new DateTime();
                $interval = $tarih1->diff($tarih2);
                $day_diff = $interval->format('%a');
                
                $recency_dates[$customer['id']] = [
                    'last_date'       => $dates[0],
                    'last_date_day'   => $day_diff,
                    'total_day_count' => $total_day_count,
                ];
                
            }
            $this->recency_data = $recency_dates;
            return $recency_dates;
            
        }
        
        public function frequency_calc($buy_list = null){
            
            $buy_list = $buy_list??$this->buy_list;
            
            $frequency_count = [];
            foreach($buy_list as $detail){
                $frequency_count[$detail['id']] = count($detail['customer_detail']['buy_list']);
            }
            
            $this->frequency_data = $frequency_count;
            return $frequency_count;
        }
        
        public function monetary_calc($buy_list = null){
            
            $buy_list = $buy_list??$this->buy_list;
            
            $monetary_count = [];
            foreach($buy_list as $customer => $detail){
                
                $total_price = 0;
                foreach($detail['customer_detail']['buy_list'] as $buy_item){
                    
                    $total_price += $buy_item['price'];
                    
                }
                
                $monetary_count[$detail['id']] = $total_price;
            }
            
            $this->monetary_data = $monetary_count;
            return $monetary_count;
        }
        
        public function score_calc(){
            
            $score_results    = [];
            $customer_results = [];
            
            $r_data = $this->recency_calc();
            $f_data = $this->frequency_calc();
            $m_data = $this->monetary_calc();
            
            $r = $this->r_calc($r_data);
            $f = $this->f_calc($f_data);
            $m = $this->m_calc($m_data);
            
            preg_match('|([a-z])([a-z])([a-z])|', mb_strtolower($this->priority, 'utf8'), $p);
            unset($p[0]);
            foreach($p as $func_name){
                $score_results[$func_name] = $$func_name;
            }
            
            foreach($score_results as $key => $customer_score){
                foreach($customer_score as $customer_id => $score){
                    $customer_results[$customer_id]['score'] = ($customer_results[$customer_id]['score']??null).$score;
                    $customer_results[$customer_id][$key]    = $score;
                }
            }
            
            arsort($customer_results);
            return $customer_results;
        }
        
        public function customer_group($score = null){
            
            $user_score = $score??$this->score_calc();
            
            $result_cat = [];
            foreach($this->customer_groups as $group_name => $detail){
                foreach($user_score as $customer_id => $customer_rfm_score){
                    
                    if(in_array($customer_rfm_score['r'], $detail['rfm']['r']) and in_array($customer_rfm_score['f'], $detail['rfm']['f']) and in_array($customer_rfm_score['m'], $detail['rfm']['m'])){
                        //echo $customer_id.' Eklendi'."\n\n";
                        $result_cat[$group_name][$customer_id] = $customer_rfm_score['score'];
                    }
                    
                }
                
            }
            
            return $result_cat;
        }
        
        public function r_calc($data = null){
            
            $data = $data??$this->recency_data;
            
            foreach($data as $customer_r_data){
                $total_day       = ($total_day??0) + $customer_r_data['last_date_day'];
                $total_day_count = ($total_day_count??0) + $customer_r_data['total_day_count'];
            }
            
            foreach($data as $customer_id => $customer_r_data){
                foreach($this->recency_formula as $score => $val){
                    
                    if($customer_r_data['last_date_day'] <= $val){
                        break;
                    }
                    
                }
                $result[$customer_id] = $score;
            }
            
            return $result;
        }
        
        public function f_calc($data = null){
            
            $data   = $data??$this->frequency_data;
            $result = [];
            
            foreach($data as $customer_id => $customer_f_data){
                foreach($this->frequency_formula as $score => $val){
                    
                    if($customer_f_data >= $val){
                        break;
                    }
                    
                }
                $result[$customer_id] = (int) $score;
            }
            
            return $result;
        }
        
        public function m_calc($data = null){
            
            $data   = $data??$this->monetary_data;
            $result = [];
            
            foreach($data as $customer_r_data){
                $total_price       = ($total_price??0) + $customer_r_data;
                $total_price_count = ($total_price_count??0) + 1;
            }
            
            foreach($data as $customer_id => $customer_r_data){
                
                foreach($this->monetary_formula as $score => $val){
                    
                    if($customer_r_data >= $val){
                        break;
                    }
                    
                }
                $result[$customer_id] = $score;
            }
            
            return $result;
            
        }
        
        private function date_order_by($dates){
            $d = $dates;
            usort($d, function($a, $b){
                if(strtotime($a) < strtotime($b))
                    return 1;
                else if(strtotime($a) > strtotime($b))
                    return -1;
                else
                    return 0;
            });
            return $d;
        }
    }