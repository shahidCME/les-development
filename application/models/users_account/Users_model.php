<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends My_model {

	public function varifiy_password($postData)
    {
        // dd($_FILES);
        if(isset($_FILES['profileimage']) && $_FILES['profileimage']['error'] == 0){
            $UploadPath = "public/images/".$this->folder."user_profile/";
            $uploadImage =  upload_single_image($_FILES,'uprofile',$UploadPath);
            // dd($uploadImage);
            $uploadImage = $uploadImage['data']['file_name'];   
            $data['update']['profileimage'] =  $uploadImage;
           
            $old_one = $postData['hidden_image'];
           
            unlink($UploadPath.$old_one);
        }

        
            $data['table'] = TABLE_USER;
            $data['update']['fname'] = $postData['fname'];
            $data['update']['lname'] = $postData['lname'];
           
            $data['update']['email'] = $postData['email'];
            $data['update']['user_gst_number'] =  $postData['user_gst_number'];
            $data['where']['id'] =  $this->session->userdata('user_id');
                /*$response =*/ $this->updateRecords($data);
        if(isset($postData['otp']) && $postData['otp']!=''){
            unset($data);
            $data['select'] = ['*'];
            $data['table'] = 'user';
            $data['where']['otp'] = $postData['otp'];
            $data['where']['id'] = $this->session->userdata('user_id');
            $data['where']['status !='] = '9';
            $re = $this->selectRecords($data);
            if(!empty($re)){
                 $data['update']['phone'] = $postData['phone'];
                 $data['update']['country_code'] = $postData['country_code'];
                 $this->updateRecords($data);
                $_SESSION['user_phone'] = $postData['phone'];

            }else{
                return false;
            }
        }



            $_SESSION['user_name'] = $postData['fname'];
            $_SESSION['user_lname'] = $postData['lname'];
            return  true;

    }

    public function update_password($postData) {
              $data['table'] = TABLE_USER;
              $data['select'] = ['id','password'];
              $data['where'] = [
                                'email' => $this->session->userdata('user_email'),
                                'password'=>md5($postData['old_pass']),
                                'vendor_id'=>$this->session->userdata('vendor_id')
                            ];
              $result = $this->selectRecords($data);
              // echo $this->db->last_query();die;
              if(!empty($result)){
                    unset($data);
                    $data['table'] = TABLE_USER;
                    $data['update']['password'] = md5($postData['new_pass']);
                    $data['where'] = ['id'=>$result[0]->id];
                    $this->updateRecords($data);
                    return true;
              }else{
                return false;
              }
    }

    public function getSelfPickupOtp($order_id){
        $data['select'] = ['*'];
        $data['table'] = 'selfPickup_otp';
        $data['where']['user_id'] = $this->session->userdata('user_id');
        $data['where']['order_id'] = $order_id;
        return $result = $this->selectRecords($data);
    }

	public function selectUserDetail(){
		$data['select'] = ['*'];
        $data['table'] = TABLE_USER;
        $data['where']['id'] = $this->session->userdata('user_id');
        return $result = $this->selectRecords($data);
	}
	
	public function updateUserDetail($postData){
		$data['table'] = TABLE_USER; 
		$data['update']['fname'] = $postData['fname'];
        $data['update']['email'] = $postData['email'];
        $data['update']['phone'] = $postData['phone'];
        $data['where']['id'] = $this->session->userdata('user_id');
        $result = $this->updateRecords($data);
        return true;	
	}

	public function getUserAddress(){
		$data['select'] = ['*'];
        $data['table'] = TABLE_USER_ADDRESS;
        $data['where'] = ['user_id'=>$this->session->userdata('user_id'),'status !=' => '9'];
        $data['order'] = 'id DESC';
        return $this->selectRecords($data);
	}

    public function getFaq(){
        $data['select'] = ['*'];
        $data['table'] = FAQ;
        return $this->selectRecords($data);
    }

	public function AddUserAddress($postData){

        $data['table'] = TABLE_USER_ADDRESS;
        $data['select'] = ['*'];
        $data['where'] = [
            'user_id'=>$this->session->userdata('user_id'),
            'status' =>'1'
        ];
        $ua = $this->selectRecords($data);
        if(empty($ua)){
            $status = '1';
        }else{
            $status = '0';
        }
        unset($data);
        $data['table'] = TABLE_USER_ADDRESS;
        $data['insert'] = [
                            'branch_id'=>$this->session->userdata('branch_id'),
        					'user_id' => $this->session->userdata('user_id'),
        					'name'=>$postData['fname'],
        					'phone'=>$postData['phone'],
        					'country'=>$postData['country'],
        					'state'=>$postData['state'],
        					'city'=>$postData['city'],
                            'google_location'=>$postData['location'],
        					'pincode'=>$postData['pincode'],
        					'address'=>$postData['address'],
        					'latitude'=>$postData['latitude'],
        					'longitude'=>$postData['longitude'],
                            'landmark' => $postData['landmark'],
        					'status'=>$status,
        					'dt_added'=>strtotime(DATE_TIME),
        					'dt_updated'=>strtotime(DATE_TIME),
        				  ];
        // echo '2';
        // print_r($data['insert']);
        // exit();
        return $this->insertRecord($data);
	}

	public function setDefaultAddress_old($id){
        $data['table'] = TABLE_USER_ADDRESS;
        $data['where']= [
            'id' => $id,
        ];
        $data['update']['status'] = '1';
        $this->updateRecords($data);
        unset($data);
		$data['select'] = ['*'];
        $data['table'] = TABLE_USER_ADDRESS;
        $data['where']= [
                    'user_id' => $this->session->userdata('user_id'),
                    'status' => '1',
                    ];
        $re = $this->selectRecords($data);
        unset($data);
            if($re){
                $data['table'] = TABLE_USER_ADDRESS;
                $data['where']= [
                            'user_id' => $this->session->userdata('user_id'),
                            'status' => '1',
                            ];
                $data['update']['status'] = '0';
                $res = $this->updateRecords($data);
            }
        unset($data);
            if($res){
                $data['table'] = TABLE_USER_ADDRESS;
                $data['where']= [
                            'id' => $id,
                            ];
                $data['update']['status'] = '1';
                $resl = $this->updateRecords($data);

            return $resl;
        }
        // unset($data);
        // foreach ($re as $key => $value) {
        // 	if($value->id == $id){
        // 		if($value->status == '0'){
        // 			$data['update']['status'] = '1';
       	// 			$data['where']['id'] = $id;
        // 		}else{
        // 			$data['update']['status'] = '0';
       	// 			$data['where']['id'] = $id;
        // 		}
        // 	$data['table'] = TABLE_USER_ADDRESS;
        // 	return $this->updateRecords($data);
        // 	}
        // 	// else{
        // 	// 	if($value->status == '1')
        // 	// 		$data['update']['status'] = '0';
       	// 	// 		$data['where']['user_id'] = $this->session->userdata('user_id');	
        // 	// }
        // }

	}

    function setDefaultAddress($id){
       $data['table'] = TABLE_USER_ADDRESS;
       $data['where']= [
        'user_id' => $this->session->userdata('user_id'),
    ];
        $data['update']['status'] = '0';
        $res = $this->updateRecords($data);
        unset($data);
        $data['table'] = TABLE_USER_ADDRESS;
        $data['where']= [
            'id' => $id,
        ];
        $data['update']['status'] = '1';
        $resl = $this->updateRecords($data);

    }

	public function getEditAddress($id){
		$data['select'] = ['*'];
        $data['table'] = TABLE_USER_ADDRESS;
        $data['where']['id'] = $id;
        return $this->selectRecords($data);
        
	}

	public function updateAddress($postData){
        // print_r($postData);die;
		$id = $postData['update_id'];
		 // $data['update'] = [
   //      					'user_id' => $this->session->userdata('user_id'),
   //      					'name'=>$postData['name'],
   //      					'phone'=>$postData['phone'],
   //      					'country'=>$postData['country'],
   //      					'state'=>$postData['state'],
   //      					'city'=>$postData['city'],
   //      					'pincode'=>$postData['pincode'],
   //      					'address'=>$postData['address'],
                            
   //      					'dt_updated'=>strtotime(DATE_TIME),
   //      				  ];
         $data['update']['user_id'] = $this->session->userdata('user_id');
         $data['update']['name'] = $postData['fname'];
         $data['update']['phone'] = $postData['phone'];
         $data['update']['country'] = $postData['country'];
         $data['update']['state'] = $postData['state'];
         $data['update']['city'] = $postData['city'];
         $data['update']['google_location'] = $postData['location'];
         $data['update']['pincode'] = $postData['pincode'];
         $data['update']['address'] = $postData['address'];
         $data['update']['landmark'] = $postData['landmark'];
    
    if(isset($postData['latitude'])){
         $data['update']['latitude'] = $postData['latitude'];
    }
    if(isset($postData['longitude']) && $postData['longitude'] != ''){
         $data['update']['longitude'] = $postData['longitude'];
    }
         $data['update']['dt_updated'] = strtotime(DATE_TIME);
        // print_r($data['update']);exit;
		 $data['table'] = TABLE_USER_ADDRESS;
         $data['where']['id'] = $id;
           
        return $this->updateRecords($data);
	}

	public function removeRecord($id){
		 $data['table'] = TABLE_USER_ADDRESS;
         $data['where']['id'] = $id;
         $return = $this->deleteRecords($data);
         unset($data);
         if($return){
            $data['table'] = TABLE_USER_ADDRESS;
            $data['select'] = ['*'];
            $data['where'] = ['user_id'=>$this->session->userdata('user_id')];
            $result = $this->selectRecords($data);
            unset($data);
            $data['table'] = TABLE_USER_ADDRESS;
            $data['where'] = ['id'=>$result[0]->id];
            $data['update'] = ['status'=>'1'];
            $result = $this->updateRecords($data);
         }
            $data['table'] = TABLE_USER_ADDRESS;
            $data['select'] = ['*'];
            $data['where'] = ['user_id'=>$this->session->userdata('user_id'),'status'=>'1'];
            $r = $this->selectRecords($data);
         return $r;
	}

    // public function getWishlist(){
    //     $data['table'] = TABLE_WISHLIST;
    //     $data['select'] = ['*'];
    //     $data['where'] = [
    //         'user_id'=>$this->session->userdata('user_id'),
    //         'branch_id'=>$this->session->userdata('branch_id'),
    //     ];
    //     return $this->selectRecords($data);
    // }

     public function getWishlist(){
        $data['table'] = 'wishlist as wl';
        $data['join'] = [
            TABLE_PRODUCT_WEIGHT.' as pw'=>['pw.id=wl.product_weight_id','LEFT'],
            TABLE_PRODUCT.' as p'=>['p.id=pw.product_id','LEFT'],
            TABLE_WEIGHT.' as w'=>['w.id = pw.weight_id','LEFT'],
            'package as pkg'=>['pkg.id = pw.package','LEFT'],
        ];
        $data['select'] = ['wl.*','pw.id as product_varient_id','pw.price','pw.price','pw.discount_price','pw.weight_no','w.name as weight_name','pw.discount_per','pkg.package as package_name','pw.max_order_qty','p.name','pw.product_id','p.branch_id','pw.quantity as available_quantity','pw.price as actual_price','w.id as weight_id'];
        $data['where']['wl.user_id'] = $this->session->userdata('user_id');
        if(isset($_SESSION['branch_id']) && $_SESSION['branch_id'] != ''){
            $data['where']['wl.branch_id'] = $this->session->userdata('branch_id');
        }
        // else{
        //     $data['where']['wl.branch_id'] = '0';
        // }
        $return =  $this->selectFromJoin($data);
        unset($data);
        foreach ($return as $k => $v) {
            $branch_id = $return[0]->branch_id;
            $product_variant_id = $v->product_varient_id;
            $data['select'] = ['quantity'];
            $data['where']['product_weight_id'] = $product_variant_id;
            $data['where']['status !='] = 9;
            $data['where']['branch_id'] = $branch_id;
            if (isset($postData['user_id']) && $postData['user_id'] != '') {
                $data['where']['user_id'] = $postData['user_id'];
            } else {
                if (isset($postData['device_id'])) {
                    $data['where']['device_id'] = $postData['device_id'];
                    $data['where']['user_id'] = 0;
                }
            }
            $data['table'] = 'my_cart';
            $result_cart = $this->selectRecords($data);
            if (count($result_cart) > 0) {
                $my_cart_quantity = $result_cart[0]->quantity;
            } else {
                $my_cart_quantity = '0';
            }

            $v->my_cart_quantity = $my_cart_quantity;
            $image = $this->getVarient_image($v->product_varient_id);
            $v->image = base_url() . 'public/images/'.$this->folder.'product_image/'.$image[0]->image;
            $v->image = str_replace(' ', '%20', $v->image);
        }  

        foreach ($return as $key => $value) {
            $this->load->model('frontend/product_model');
            $addQuantity = $this->product_model->findProductAddQuantity('',$value->product_weight_id);
            $value->addQuantity = $addQuantity;
        }
        // dd($return);
        return $return;
       }

    public function getVarient_image($varient_id){
        $data['table'] = TABLE_PRODUCT_IMAGE;
        $data['where'] = ['product_variant_id'=>$varient_id];
        $data['select'] = ['*'];
        return $this->selectRecords($data);

     }

    public function defaultProduct($id){

            $vendor_id = $this->session->userdata('branch_id');
            $data['table'] = TABLE_PRODUCT . " as p";
            $data['select'] = ['pw.discount_price'];
            $data['join'] = [
                TABLE_PRODUCT_WEIGHT .' as pw'=>['p.id = pw.product_id','LEFT']
            ];
            $data['where'] = ['p.status !=' => '9','p.id'=>$id,'p.branch_id'=>$vendor_id];      
            $data['groupBy'] =['p.id'];
            return  $this->selectFromJoin($data);
    }

    public function removeItemFromWishlist($postData){
        $id = $this->utility->safe_b64decode($postData['wishlist_product_id']);
        $data['table'] = TABLE_WISHLIST;
        $data['where'] = [
            'id'=>$id,
            'branch_id'=>$this->session->userdata('branch_id'),
            'user_id'=>$this->session->userdata('user_id'),
        ];
        return $this->deleteRecords($data);
    }

    public function checkCurrentUserLoginType(){
        $user_id = $this->session->userdata('user_id');
        $data['table'] =  TABLE_USER;
        $data['select'] = ['login_type'];
        $data['where'] = ['id'=>$user_id];
        $res = $this->selectRecords($data);
        return $res[0]->login_type;
    }

     public function getDetails($varient_id){
        $data['table'] =  TABLE_PRODUCT_WEIGHT.' pw';
        $data['select'] = ['pw.weight_no','w.name','pkg.package'];
        $data['join'] = [
            TABLE_WEIGHT .' w'=>['pw.weight_id=w.id','LEFT'],
            'package as pkg' =>['pw.package=pkg.id','LEFT']
        ];
        $data['where'] = [
            'pw.id'=>$varient_id,'pw.status!='=>'9',
            'w.status!='=>'9',
            'pw.branch_id'=>$this->session->userdata('branch_id'),
        ];
        return $this->selectFromJoin($data);
    }
    
    public function selectOrders(){
           
        $data['table'] = TABLE_ORDER; 
        $data['select'] = ['*'];
        $data['where'] = [
                        'user_id'=> $this->session->userdata('user_id'),
                        'status !=' => '9',
                        'branch_id' => $this->session->userdata('branch_id')
                     ];
        $data['order'] = 'dt_updated DESC';
        return $this->selectRecords($data);
    }

    public function getBranchDetails($branch_id){
        $data['table'] = TABLE_BRANCH; 
        $data['select'] = ['name','address','location'];
        $data['where'] = [
                        'id'=>$branch_id ,
                        'status' => '1',
                     ];
        return $this->selectRecords($data);   
    }

    public function getUserDetails(){
        $data['table'] = TABLE_USER; 
        $data['select'] = ['fname','lname','phone','login_type'];
        $data['where'] = [
                        'id'=> $this->session->userdata('user_id'),
                        'status !=' => '9',
                     ];
        return $this->selectRecords($data); 
    }

        public function sendOtpAccount($postData){


        $userData['select'] = ['*'];
        $userData['table'] = 'user';
        $userData['where'] = ['country_code' => $postData['country_code'],'phone'=>$postData['phone'],'id !=' => $this->session->userdata('user_id'),'status !=' =>'9','vendor_id'=>$this->session->userdata('vendor_id')];
        $userDetail = $this->selectRecords($userData);
        if(!empty($userDetail) ){
            $response["success"] = 0;
            $response["message"] = "This mobile number is linked with another account";
            return $response;
        }
        $otp = rand(1111,9999);

        $country_code = $postData['country_code'];
        $check_str = str_split($country_code); 
        if($check_str[0] != '+'){
            $country_code = '+'.$country_code;
        }
        $mobile = $postData['phone'];
        $mobile_number = $country_code.''.$mobile;
        // $this->api_model->sendOtp($mobile_number,$otp);

        $data['update'] = ['otp'=>$otp];
        $data['where'] =['id'=>$this->session->userdata('user_id')];
        $data['table'] = 'user';
        $this->updateRecords($data);
        
        $response["success"] = 1;
        $response["message"] = "successfully sent otp on your mobile";
        $response['data'] = $otp;
        return $response;

    }

    public function getVedorDetails(){
        $vendor_id = $this->session->userdata('vendor_id');
        $data['table'] = 'vendor'; 
        $data['select'] = ['login_type'];
        $data['where'] = [
                        'id'=> $vendor_id,
                     ];
        return $this->selectRecords($data); 
    }

    public function delete_user()
    {

        $user_id = $this->session->userdata('user_id');
        $data['select'] = ['*'];
        $data['where'] = ['order_status <'=>'8','user_id'=>$user_id,'branch_id' =>$this->session->userdata('branch_id')];
        $data['table'] = TABLE_ORDER;
        $checkOrder = $this->selectRecords($data);
        if(!empty($checkOrder)){
            $this->utility->setFlashMessage('danger',"Please wait for deliver current order or cancle ongoing order");
            $response["success"] = 0;
            $response["message"] = "Please wait for deliver current order or cancle ongoing order";
            // return true;
            // redirect(base_url().'home');
            return $response;
        }
        unset($data);
        $data['where'] = ['user_id'=>$user_id];
        $data['table'] = TABLE_MY_CART;
        $this->deleteRecords($data);
        unset($data);
        $data['where'] = ['user_id'=>$user_id];
        $data['table'] = 'device';
        $this->deleteRecords($data);
        unset($data);
        $data['update'] = ['status'=>'9'];
        $data['where'] = ['id'=>$user_id];
        $data['table'] = TABLE_USER;
        
        $this->updateRecords($data);
        $this->session->unset_userdata('My_cart');
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('user_name');
        $this->session->unset_userdata('user_lname');
        $this->session->unset_userdata('user_email');
        $this->session->unset_userdata('user_phone');
        $this->utility->setFlashMessage('danger',"User Account is permanant deleted");
        $response["success"] = 1;
        $response["message"] = "User Account is permanant deleted";  
        return $response;
        // return true;
        // redirect(base_url().'home');
    }
}
?>