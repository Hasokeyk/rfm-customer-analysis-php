<?php
    
    
    namespace consumer_rfm;
    
    
    class customer_rfm{
        
        public $buy_list = null;
        
        private $recency_data   = [];
        private $frequency_data = [];
        private $monetary_data  = [];
        
        public $recency_formula   = [
            1 => 0.25,
            2 => 0.50,
            3 => 0.75,
            4 => 1,
        ];
        public $frequency_formula = [
            4 => 0.25,
            3 => 0.50,
            2 => 0.75,
            1 => 1,
        ];
        public $monetary_formula  = [
            4 => 0.25,
            3 => 0.50,
            2 => 0.75,
            1 => 1,
        ];
        
        public $priority = 'RFM';
        
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
                
                $recency_dates[$customer['id']] = [
                    'last_date'       => $dates[0],
                    'last_date_day'   => date('z', strtotime($dates[0])) + 1,
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
                $score_results[] = $$func_name;
            }
            
            foreach($score_results as $id => $customer_score){
                foreach($customer_score as $customer_id => $score){
                    $customer_results[$customer_id] = (int) ($customer_results[$customer_id]??null).$score;
                }
            }
            
            arsort($customer_results,SORT_NATURAL);
            return $customer_results;
        }
        
        public function r_calc($data = null){
            
            $data = $data??$this->recency_data;
            
            foreach($data as $customer_r_data){
                $total_day       = ($total_day??0) + $customer_r_data['last_date_day'];
                $total_day_count = ($total_day_count??0) + $customer_r_data['total_day_count'];
            }
            
            foreach($data as $customer_id => $customer_r_data){
                foreach($this->recency_formula as $score => $val){
                    $percent = ($total_day / $total_day_count) * $val;
                    if($percent <= $customer_r_data['last_date_day']){
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
                    $percent = ($total_order / $total_order_count) * $val;
                    if($percent >= $customer_r_data){
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
                    $percent = ($total_price / $total_price_count) * $val;
                    
                    if($percent >= $customer_r_data){
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