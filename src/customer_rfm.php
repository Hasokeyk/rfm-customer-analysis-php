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
            3 => 75,
            2 => 100,
            1 => 999,
        ];
        public $frequency_formula = [
            5 => 999,
            4 => 8,
            3 => 5,
            2 => 3,
            1 => 1,
        ];
        public $monetary_formula  = [
            5 => 9999,
            4 => 500,
            3 => 250,
            2 => 100,
            1 => 10,
        ];
        
        public $priority = 'RFM';
        
        public $customer_groups = [
            'best_customer'             => [
                'name' => 'Best Customer',
                'rfm'  => [
                    'r' => 1,
                    'f' => 1,
                    'm' => 1,
                ],
            ],
            'loyal_customer'            => [
                'name' => 'Loyal Customer',
                'rfm'  => [
                    'r' => 1,
                    'f' => 1,
                    'm' => 1,
                ],
            ],
            'new_customer'              => [
                'name' => 'New Customer',
                'rfm'  => [
                    'r' => 1,
                    'f' => 1,
                    'm' => 1,
                ],
            ],
            'promising_customer'        => [
                'name' => 'Promising',
                'rfm'  => [
                    'r' => 1,
                    'f' => 1,
                    'm' => 1,
                ],
            ],
            'warning_customer'          => [
                'name' => 'Customer Needs Attention',
                'rfm'  => [
                    'r' => 1,
                    'f' => 1,
                    'm' => 1,
                ],
            ],
            'about_to_sleep_customer'   => [
                'name' => 'About to Sleep',
                'rfm'  => [
                    'r' => 1,
                    'f' => 1,
                    'm' => 1,
                ],
            ],
            'cannot_lose_them_customer' => [
                'name' => 'Cannot Lose Them',
                'rfm'  => [
                    'r' => 1,
                    'f' => 1,
                    'm' => 1,
                ],
            ],
            'lost_customer'             => [
                'name' => 'Lost Customers',
                'rfm'  => [
                    'r' => 1,
                    'f' => 1,
                    'm' => 1,
                ],
            ],
        ];
        
        public $score = null;
        
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
    
                $tarih1= new DateTime($dates[0]);
                $tarih2= new DateTime();
                $interval= $tarih1->diff($tarih2);
                $day_diff =  $interval->format('%a');
                
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
                    $customer_results[$customer_id] = ($customer_results[$customer_id]??null).$score;
                }
            }
        
            arsort($customer_results, SORT_NATURAL);
            return $customer_results;
        }
        
        /*
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
            
            print_r($score_results);
            
            foreach($score_results as $key => $customer_score){
                $c_id = 0;
                $s = '';
                foreach($customer_score as $customer_id => $score){
                    $c_id = $customer_id;
                    $s .= $score;
                }
                $customer_results[$c_id]['total'] = $s;
                //$customer_results[$c_id][$key]    = $customer_score;
            }
            
            //arsort($customer_results, SORT_NATURAL);
            $this->score = $customer_results;
            return $customer_results;
        }
        */
        
        public function customer_group($score = null){
            
            $score = $score??$this->score_calc();
            
            $uniq = array_unique($score);
            
            arsort($uniq);
            return $uniq;
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
            
            foreach($data as $customer_r_data){
                $total_order       = ($total_order??0) + $customer_r_data;
                $total_order_count = ($total_order_count??0) + 1;
            }
            
            foreach($data as $customer_id => $customer_r_data){
                foreach($this->frequency_formula as $score => $val){
    
                    if($customer_r_data >= $val){
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