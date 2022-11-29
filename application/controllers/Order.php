<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Order extends Vendor_Controller
{
    function __construct(){
        parent::__construct();
        $vendor_id = $this->session->userdata['id'];
        $this->load->model('order_model','this_model');
    }

    public function index(){
        $data['table_js'] = array('order.js');
        $data['start'] = array('ORDER.table()');
        $data['order_result'] = $this->this_model->getOrderList();
        // echo '<pre>';
        // print_r($data['order_result']);die;
        $this->load->view('order_list',$data);
    }

    public function getOrderListAjax(){
        if($this->input->post()){
            echo getOrderListAjax($this->input->post());
        }
    }
    
    public function order_list()
    {
        $data['order_result'] = $this->this_model->getOrderList();
        $this->load->view('order_list',$data);
    }
    public function order_detail()
    {
        $vendor_id = $this->session->userdata['branch_vendor_id'];
        $order_id = $this->utility->decode($_GET['id']);
        if(isset($_GET['vendor_id'])  &&  $_GET['vendor_id'] != ""){
           $vendor_id = $this->utility->decode($_GET['vendor_id']);
        }
        $data['order_detail_result'] = $this->this_model->order_detail_query($order_id);
        @$user_id =$data['order_detail_result'][0]->user_id;
        $data['user_detail'] = $this->this_model->userDetails($user_id);
        $data['user'] = $this->this_model->user($user_id);
        // print_r($data['user']);die;
        $data['vendor_detail'] = $this->this_model->vendorDetail($vendor_id);
        
        $data['order_detail'] = $this->this_model->orderDetails($order_id);
        $total_gst = 0 ;
        foreach ($data['order_detail_result'] as $key => $value) {
            $this->load->model('api_model');
            $gst = $this->api_model->getProductGst($value->product_id);
            $gst_amount = ($value->discount_price * $gst)/100;
            $total_gst += $gst_amount * $value->quantity;
        }
        
        $data['total_gst'] = $total_gst;
        $data['getcurrency'] = $this->this_model->queryCurrency();
        $this->load->view('order_invoice',$data);
    }

    public function order_detail_list()
    {
        $this->load->view('order_detail_list');
    }

    public function single_delete_order()
    {
        $this->this_model->single_delete_order();
    }
    public function multi_delete_order()
    {
        $this->this_model->multi_delete_order();
    }
    public function single_delete_order_detail()
    {
        $this->this_model->single_delete_order_detail();
    }
    public function multi_delete_order_detail()
    {
        $this->this_model->multi_delete_order_detail();
    }
    public function change_order_status(){
        $this->this_model->change_order_status();
    }
    public function verify_otp()
    {
        if($this->input->post('isSelfPickup') == '0'){
           $r =  $this->this_model->verify_otp();
            $status = $this->this_model->checkLatestOrderStaus($this->input->post());
            if($r == 0 && $status[0]->order_status >= 4){
                $this->this_model->verify_otp_selfPickup($this->input->post());    
            }else{
                echo "0";
                exit;
            }
        }else{
            $this->this_model->verify_otp_selfPickup($this->input->post());
        }

    }

       public function getOrderlog(){
        if($this->input->post()){
            $res = $this->this_model->getOrderLog($this->input->post());
            $tr = '';
            foreach ($res as $key => $value) {
                if($value->order_status == '1'){
                    $status = 'New Order';
                }
                if($value->order_status == '2'){
                    $status = 'pending';
                }
                if($value->order_status == '3'){
                    $status = 'Ready';
                }
                if($value->order_status == '4'){
                    $status = 'Pickup';
                }
                if($value->order_status == '5'){
                    $status = 'On the Way';
                }
                if($value->order_status == '8'){
                    $status = 'Delivered';
                }
                if($value->order_status == '9'){
                    $status = 'Cancel';
                }
                $tr .= '<tr><td>'.($key+1).'</td>
                        <td>'.$status.'</td>
                        <td>'.$value->dt_created.'</td></tr>';
            }
              // $tr .= '</tr>';

             echo json_encode(['order_log'=>$tr]); 
        }
    }

    public function order_report(){
        $date = '';
        $to_date = '';
        $postData = [];
        if($this->input->post()){
            $date =  $this->input->post('orderReportDate');  
            $to_date =  $this->input->post('to_date');
            $postData = $this->input->post();
        }
        $data['report'] = $this->this_model->getOrderReportForDate($postData);
        $data['date'] = $date;
        $data['to_date'] = $to_date;
        // echo $data['date'] ;die;
        $this->load->view('order_report',$data);
    }

     public function refundpayment(){

        if($this->input->post()){
            $paymentMethod = $this->input->post('paymentMethod');
            $order_id = $this->input->post('id');
            if($paymentMethod == 3){
             // paytm
                $respons = $this->this_model->refundPaymentPaytm($order_id);
            }
            if($paymentMethod == 1){
             // razar
                $respons = $this->this_model->refundPaymentRazar($order_id);
            }
            if($paymentMethod == 2){
                $respons = $this->this_model->refundPaymentStripe($order_id);   
            }
            if($paymentMethod == 0){
                
            }
            echo json_encode($respons);
        }

   } 

      public function order_summary(){
        $data['table_js'] = array('order_summary.js');
        $data['start'] = array('ORDER_SUMMARY.table()');
        $this->load->view('order_summary',$data);
   }

   public function getOrderSummaryListAjax(){
        if($this->input->post()){
            echo getOrderSummaryListAjax($this->input->post());
        }
   }

   public function generate_order_summary_report(){
        if($this->input->post()){
            $re = $this->this_model->make_datatables_order_summary($this->input->post());
            $this->load->library('excel');
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
            // set Header
            $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'User Address');
            $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Order By');
            $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Order No');
            $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Order Date');
            $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Username');       
            $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Total Amount');       
            $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Payment Type');       
            $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Order Status');       
            // set Row
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setSize(12);
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->getStartColor()->setARGB('#333');

            $rowCount = 2;
            foreach ($re as $list) {
                if($list->order_status=='1'){
                  $order_status = "New order";
                }elseif($list->order_status=='2'){
                  $order_status = "Pending";
                }elseif($list->order_status=='3'){
                  $order_status = "Ready";
                }elseif($list->order_status=='4'){
                  $order_status = "Pickup";
                }elseif($list->order_status=='5'){
                  $order_status = "On the way";
                }elseif($list->order_status=='8'){
                  $order_status = "Delivered";
                }else{
                  $order_status = "Cancelled";
                } 
            if($list->payment_type == '0'){$payment_type = 'COD';}elseif($list->payment_type == '1'){$payment_type = 'Credit-card';}else{$payment_type = 'Wallet Balance';};
                $orderDate = date('Y m d H:i A',$list->dt_added);
                $username = $list->fname.' '.$list->lname;
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $list->address);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $orderBY);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $list->order_no);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $orderDate);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $username);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, '₹ '.$list->payable_amount);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $payment_type);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $order_status);
                $rowCount++;
            }

            $filename = "order_summary". date("Y-m-d-H-i-s").".xls";
            header('Content-Type: application/vnd.ms-excel'); 
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0'); 
            ob_start();
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');  
            $objWriter->save('php://output'); 
            $xlsData = ob_get_contents();
            ob_end_clean();
            $response =  array(
            'status' => TRUE,
            'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData),
            // 'filename'=>$filename
            );
            die(json_encode($response));
        }
   }

   public function sell_report(){
         $data['table_js'] = ['sell_report.js'];
         $data['most_sell'] = $this->this_model->getMostSell();
         // echo $this->db->last_query();
         $this->load->view('most_sell_report',$data);
       
       }

       public function generate_most_sell_report(){
        $most_sell = $this->this_model->getMostSell();
            // dd($most_sell);

        // if($this->input->post()){
            $re = $this->this_model->getMostSell();
            $this->load->library('excel');
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
                // set Header
            $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Product Name');
            $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Variant');
            $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Sell Quantity');       
                // set Row
            $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setSize(12);
            $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFill()->getStartColor()->setARGB('#333');

            $rowCount = 2;
            foreach ($re as $list) {
              $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $list->product_name);
              $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $list->weight_no.''.$list->weight_name);
              $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $list->TotalQuantity);
              $rowCount++;
          }

          $filename = "sell_report". date("Y-m-d-H-i-s").".xls";
          header('Content-Type: application/vnd.ms-excel'); 
          header('Content-Disposition: attachment;filename="'.$filename.'"');
          header('Cache-Control: max-age=0'); 
          ob_start();
          $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');  
          $objWriter->save('php://output'); 
          $xlsData = ob_get_contents();
          ob_end_clean();
          $response =  array(
            'status' => TRUE,
            'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData),
            'filename'=>$filename
        );
          die(json_encode($response));
      // }
  }

  public function user_sell_report(){
    $data['table_js'] = ['user_sell_report.js'];
    $data['user_sell_report'] = $this->this_model->user_sell_report();
    // dd($data['user_sell_report']);
    $this->load->view('user_sell_report',$data);
  }

  public function generate_user_sell_report(){
            $re = $this->this_model->user_sell_report();
            $this->load->library('excel');
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
                // set Header
            $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'UserName');
            $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Order No.');
            $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Purchased Value');       
            $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Purchased Date');       
            // set Row
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(22);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setSize(12);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->getStartColor()->setARGB('#333');

            // set cell value alignment
            $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $rowCount = 2;
            foreach ($re as $list) {
              $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $list->fname .' '.$list->lname);
              $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $list->order_no);
              $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $list->payable_amount);
              $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $list->dt_added);
              $rowCount++;
          }

          $filename = "user_sell_report". date("Y-m-d-H-i-s").".xls";
          header('Content-Type: application/vnd.ms-excel'); 
          header('Content-Disposition: attachment;filename="'.$filename.'"');
          header('Cache-Control: max-age=0'); 
          ob_start();
          $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');  
          $objWriter->save('php://output'); 
          $xlsData = ob_get_contents();
          ob_end_clean();
          $response =  array(
            'status' => TRUE,
            'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData),
            'filename'=>$filename
        );
          die(json_encode($response));
  }
}

?>