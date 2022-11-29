    <?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Product extends Vendor_Controller
{
     function __construct(){

        parent::__construct();
        // $vendor_id = $this->session->userdata['id'];
        $this->load->model('product_model','this_model');
        // echo 1;exit;

    }
    
    public function index()
    {
        $data['table_js'] = array('product.js');
        $data['start'] = array('PRODUCT.table2()');
        $this->load->view('supplier_list_by_id',$data);
    }

    public function product_list()
    {
        // echo '1';die;
        $data['table_js'] = array('product.js');
        $data['start'] = array('PRODUCT.table()');
        $this->load->view('product_list',$data);
    }

    public function getProductListAjax(){
        if($this->input->post()){
            echo getProductListAjax($this->input->post());
        }
    }

    public function product_profile()
    { 
        $data['category_result'] = $this->this_model->getCategory();
        $data['brand_results'] = $this->this_model->getBrand();
        $data['subcategory_result'] = $this->this_model->getSubcategory();
        $id = '';
        $tags = '';
        $category_id = 0;
        if(isset($_GET['id'])){
            $id = $this->utility->decode($_GET['id']); //id = product_id
            $data['result'] = $this->this_model->getGetProductById($id);
            $category_id = $data['result']['category_id'];
            $brand_id = $data['result']['brand_id'];
            $subid = $data['result']['subcategory_id'];

            $data['cat_result'] = $this->this_model->getCategory($category_id);
            $data['bra_result'] = $this->this_model->getBrand($brand_id);

            $data['brand_results'] = $this->this_model->getBrandByCategoryId($category_id);
            $data['subcate_result'] = $this->this_model->getSubcategory($subid);
            $data['subcategory_result'] = $this->this_model->getSubcategoryOfCategoryId($category_id);
            $search_results = $this->this_model->searchProductByTag($id);
            // dd($search_results);
            if($search_results){
                foreach($search_results as $val){
                    $tags .= $val->name.','; 
                }
            }
        }

        $data['id'] = $id;
        $data['category_id'] = $category_id;
        $data['tags'] = $tags;
        $this->load->view('product_profile',$data);
    }
    
    public function product_add_update(){
        $this->this_model->product_add_update();
    }
    public function get_brand(){
        $this->this_model->get_brand();
    }
    public function get_subCategory(){
        $this->this_model->get_subCategory();
    }
    public function single_delete_product(){
        $this->this_model->single_delete_product();
    }
    public function multi_delete_product(){
        $this->this_model->multi_delete_product();
    }
    public function product_image_add_update(){
        $this->this_model->product_image_add_update();
    }
    public function single_delete_product_image(){
        $this->this_model->single_delete_product_image();
    }
    public function product_weight_add_update(){
        // print_r($_SESSION);die;
        $this->this_model->product_weight_add_update();
    }
    public function single_delete_product_weight(){
        $this->this_model->single_delete_product_weight();
    }
    public function multi_delete_product_weight(){
        $this->this_model->multi_delete_product_weight();
    }

    public function updateProduct(){
        $this->this_model->update_without_gst();
    }
    
    public function product_image_list()
    {
        $this->load->view('product_image_list');
    }

    public function product_weight_list(){
        $data['table_js'] = array('product.js');
        $data['start'] = array('PRODUCT.table3()');
        $data['product_id'] = $this->utility->decode($_GET['product_id']);
        $data['getNameOfProduct'] = $this->this_model->getProductName($data['product_id']);
        // print_r($data['getNameOfProduct']);die;
        $this->load->view('product_weight_list',$data);
    }

     public function getProductWeightListAjax(){
        if($this->input->post()){
            echo getProductWeightListAjax($this->input->post());
        }
    }

    public function product_weight_profile()
    {
        $data['weight_result'] = $this->this_model->getWeightResult();
        $data['package_results'] = $this->this_model->getPackageResults();
        $data['product_id'] = $this->utility->decode($_GET['product_id']);
        if(isset($_GET['id'])){
            $id = $this->utility->decode($_GET['id']);
            $data['product_id'] = $this->utility->decode($_GET['product_id']);
            $data['result'] = $this->this_model->getProductWeightById($id);
            $weight_id = $data['result']['weight_id'];
            $data['wei_result'] = $this->this_model->getWeightResult($weight_id);
            $data['product_image'] = $this->this_model->GetProductImage($id);
        }
        // dd($data['weight_result']);
        $this->load->view('product_weight_profile',$data);
    }
    public function imagedrag(){
        $imageIdsArray = $_POST['imageIds'];

            $count = 1;
            foreach ($imageIdsArray as $id) {
               echo "UPDATE product_image SET image_order='$count' WHERE id='$id'";
                $this->db->query("UPDATE product_image SET image_order='$count' WHERE id='$id'");
                
                $count ++;
            }
            // echo "successfully";
    }

    public function check_product_varient(){
        $this->this_model->checkProductVarient();
    }

    public function check_product_varient_in_order(){
        $this->this_model->check_product_varient_in_order();
    }

    public function make_product_active(){
        $product_id = $this->utility->decode($_GET['product_id']);
        $res = $this->this_model->make_product_active($product_id);
        if($res){
           $this->session->set_flashdata('msg', 'Product activated successfully');
            // $this->utility->setFlashMessage('success','Product activated successfully');
       }else{
           $this->session->set_flashdata('msg_danger', 'Somthing Went Wrong');
            // $this->utility->setFlashMessage('danger','Somthing Went Wrong');
       }
       redirect(base_url().'product/product_list');

   }

   public function check_for_hard_delete(){
        $this->this_model->checkForHardDelete();
   }

   public function parmanentDeleteProduct(){
        $this->this_model->parmanentDeleteProduct();
   }
}
?>