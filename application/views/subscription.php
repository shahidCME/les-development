<?php
include('header.php');
error_reporting(0);
$id = $this->utility->decode($_GET['id']);

$vendor_id = $this->session->userdata['id'];

$query = $this->db->query("SELECT * FROM set_default WHERE request_id='4'");
$result = $query->row_array();

?>
    <style>
        .required{
            color: red;
        }
        .img_preview {
            width: 300px;
            position: relative;
            display: none;
        }
        img#img_preview {
            width: 100%;
        }
        .overlay{
            position: absolute;
            width: 100%;
            height: 100%;
        }
        .im_progress {
            position: absolute;
            width: 100%;
            opacity: 0.5;
        }
        .loader_img{
            position: absolute;
            top: 50%;
            left: 50%;
        }
    </style>
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
            <!-- page start-->
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <!--breadcrumbs start -->
                    <ul class="breadcrumb">
                        <li class="active"><a href=""><i class="fa fa-home"></i> <a href="<?php echo base_url().'admin/dashboard'; ?>">Home</a> / Subscription  <?php if($result['id']!=''){
                                    echo "Update";
                                }else{
                                    echo "Add";
                                } ?></a></li>
                    </ul>
                    <!--breadcrumbs end -->
                </div>
            </div>
            <div id="msg">
                <?php if ($this->session->flashdata('msg') && $this->session->flashdata('msg') != '') { ?>
                    <div class="alert alert-success fade in">
                        <strong>Success!</strong> <?php echo $this->session->flashdata('msg');; ?>
                    </div>
                <?php } ?>
            </div>
            <div class="row">
                <!--Left Part-->
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <section class="panel">
                        <header class="panel-heading">
                            <?php if($result['id']!=''){
                                echo "Update Subscription Pack Value";
                            }else{
                                echo "Add Subscription Pack Value";
                            } ?>

                        </header>
                        <form role="form" method="post" action="<?php echo base_url().'setting/subscription_add'; ?>" name="subscription" id="subscription" enctype="multipart/form-data">
                            <input type="hidden" id="id" name="id" value="<?php echo $result['id']; ?>">
                            <div class="panel-body">
                                <div class="col-md-12 col-sm-12 col-xs-12 padding-zero">
                                    <div class="col-lg-12 col-md-6 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="name" id="cat" class="margin_top_label">Subscription :<span class="required" aria-required="true"> * </span></label>
                                            <input type="text" class="form-control margin_top_input" id="subscription_value" name="subscription_value" placeholder="Subscription Pack value" value="<?php echo $result['value']; ?>">
                                        </div>


                                    </div>

                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                        <?php if($result['id']!=''){
                                            $btn = "Update ";
                                        }else{
                                            $btn ="Add ";
                                        } ?>

                                        <a href="<?php echo base_url().'admin/dashboard'; ?>" style="float: right; margin-right: 10px;" id="delete_user" class="btn btn-danger">Cancel</a>
                                        <input type="submit" class="btn btn-info pull-right margin_top_label" value="<?php echo $btn.'Subscription Pack Value'; ?>" name="submit">
                                    </div>
                                </div>
                        </form>
                    </section>
                </div>

                <!--Map Part-->
            </div>
            <!-- page end-->
        </section>
    </section>
    <!--main content end-->
    <style> label.error { color: red; font-weight: 500; } </style>
    <script src="<?php echo base_url(); ?>public/js/jquery-1.8.3.min.js"></script>
    <!-- <script src="<?php echo base_url(); ?>public/js/jquery.validate.min.js"></script> -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/additional-methods.min.js"></script>
    <script type="text/javascript">

        setTimeout(function () { $('#msg').hide(); }, 4000);


        $('#subscription').validate({
            rules: {
                subscription_value: {
                    required: true,
                    number: true,
                }
            },
            messages: {
                subscription_value: {
                    required: "Please enter subscription pack value",
                    number: "Please enter valid subscription pack value"
                }
            },
            error: function(label) {
                placement();
            },
            submitHandler: function (form) {
                $('.btn').attr('disabled','disabled');
                $(form).submit();
            }
        });




    </script>
<?php include('footer.php'); ?>