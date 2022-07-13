<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Billing extends CI_Controller {

	public function __construct() {
        parent::__construct();
        if (!isset($this->session->emr_login))
         {
		      	redirect('login');
         }
         $this->load->model('Common_model');
         $this->load->model('Billing_model');
    }
     public function ajax_call(){
        $param=$_REQUEST;
        $response=$this->Billing_model->ajax_call($param);
        echo json_encode($response);
 }
	public function index()
	{
      $data['title']='EMR::Billing';
      $data['activecls']='Billing';
      $var_array=array($this->session->userdata('office_id'));
      $data['mrdnos']=$this->Common_model->getpateintmrdnos($var_array);
      $data['doctors']=$this->Common_model->getdoctors($var_array);
      $data['modeofpays']=$this->Common_model->getmodeofpay($var_array);
      $data['insurancecompanys']=$this->Common_model->getinsurance_company($var_array);
      $data['chargeslist']=$this->Common_model->getchargeslist($var_array);
      $content=$this->load->view('transaction/billing/insert',$data,true);
      $this->load->view('includes/layout',['content'=>$content]);  
	}

     public function getparticularsdetails()
  {
     $this->form_validation->set_rules('getid', 'Particulars', 'trim|required|min_length[1]|max_length[100000]|numeric');
     $this->form_validation->set_rules('chargetype_id', 'Charge Type', 'trim|required|min_length[1]|max_length[100000]|numeric');
      if($this->form_validation->run() == TRUE)
      {
        $particular_id=trim(htmlentities($this->input->post('getid')));
        $chargetype_id=trim(htmlentities($this->input->post('chargetype_id')));
          $getresult=$this->Common_model->getparticularsmodel($chargetype_id,$particular_id,$this->session->userdata('office_id'));
          if($getresult)
          {
            //$getdis=$this->Common_model->getDiscountmodel($chargetype_id,$this->session->userdata('office_id'));
            echo json_encode(array('msg' =>$getresult,'error'=>'','error_message' =>''));
          }
          else
          {
              echo json_encode(array('msg' =>'','error'=> 'Failed to get data','error_message' =>''));
          }
        
      }
      else
      {
           echo json_encode(array('msg'=>'','error'=> '','error_message' => $this->form_validation->error_array()));
      }
  }
     private function fetch_data() 
    {
       
       $return=array(
               "billing_master"=>array(
                   "patient_registration_id"=>$this->input->post('patient_registration_id'),
                   "billing_date"=>date('Y-m-d',strtotime($this->input->post('bill_date'))),
                   "billing_time"=>($this->input->post('bill_time'))?$this->input->post('bill_time'):date('H:i:s'),
                   "insurance_company_id"=>$this->input->post('insurance_company_id'),
                   "doctor_id"=>$this->input->post('doctor_id'),
                   'bill_amount'=>$this->input->post('billamount'),
                   'advanced_amount'=>$this->input->post('advancedamount'),
                   'modeofpay_id'=>$this->input->post('modeofpay_id'),
                   'grand_total'=>$this->input->post('net_amount'),
                   'parent_id'=>$this->session->userdata('parent_id'),
                   'login_id'=>$this->session->userdata('login_id'),
                   'office_id'=> $this->session->userdata('office_id')
                  
               ),
             "billing_detail"=>array(
                 "charge_id"=>$this->input->post('chargesid'),
                 "particulars_id"=>$this->input->post('particularsid'),
                 "qty"=>$this->input->post('quantity'),
                 "rate"=>$this->input->post('rate'),
                 "disamt"=>$this->input->post('disamt'),
                 "disper"=>$this->input->post('disper'),
                 "amount"=>$this->input->post('amount'),
                 "calrow_id"=>$this->input->post('calrow_id')
             )
           
           );
            return $return;
    }
  public function savedata()
  {
      $this->form_validation->set_rules('patient_registration_id', 'Select Patient', 'trim|required|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('insurance_company_id', 'Insurance Company', 'trim|min_length[1]|max_length[30]|numeric');
      $this->form_validation->set_rules('doctor_id', 'Dotors', 'trim|required|min_length[1]|max_length[30]|numeric');
      $this->form_validation->set_rules('particularsid[]', 'Particular', 'trim|required|min_length[1]|max_length[30]|numeric');
      $this->form_validation->set_rules('chargesid[]', 'Charge Type', 'trim|required|min_length[1]|max_length[30]|numeric');
      $this->form_validation->set_rules('quantity[]', 'Quantity', 'trim|required|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('rate[]', 'Rate', 'trim|required|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('disamt[]', 'Discount Amount', 'trim|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('disper[]', 'Discount Percentage', 'trim|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('amount[]', 'Amount', 'trim|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('modeofpay_id', 'Modeofpay', 'trim|required|min_length[1]|max_length[30]|numeric');
      if($this->form_validation->run() == TRUE)
      {
          $data=$this->fetch_data();
          $getresult=$this->Billing_model->savedata($data);
          if($getresult)
          {
            echo json_encode(array('msg' =>'Saved Successfully','error'=>'','error_message' =>''));
          }
          else
          {
              echo json_encode(array('msg' =>'','error'=> 'Failed to save','error_message' =>''));
          }
        
      }
      else
      {
           echo json_encode(array('msg'=>'','error'=> '','error_message' => $this->form_validation->error_array()));
      }
  }

  public function getsavedata()
  {
      $this->form_validation->set_rules('getid', 'Edit ID', 'trim|required|min_length[1]|max_length[1000]|numeric');
      if($this->form_validation->run() == TRUE)
      {
        $getid=trim(htmlentities($this->input->post('getid')));
        $var_array=array($getid,$this->session->userdata('office_id'));
        $chk_duplication=$this->Billing_model->editcheckbillingentry($var_array);
        if($chk_duplication[0]['cnt']==1)
        {
          $html='';
          $var_array_child=array($getid);
          $getmasterdata=$this->Billing_model->Getmastertable($var_array);
          $getdetailsdata=$this->Billing_model->Getdetailstable($var_array_child);
          $sl=1;
          if($getdetailsdata)
          {
              foreach ($getdetailsdata as $data) {

               $getparticularname=$this->Common_model->getparticularsmodel($data['charge_id'],$data['particulars_id'],$this->session->userdata('office_id'));

                $html.='<tr>
                         <td><a href="#" onclick="$(this).parent().parent().remove();calcnet();chkcount();"><button class="btn btn-danger btnDelete btn-sm"><i class="la la-trash"></i></button></a></td>
                         <td>'.$getparticularname[0]['name'].'</td>
                        <td><input type="number" step="any" name="quantity[]" id="quantity_'.$sl.'" class="form-control grid_table" value="'.$data['qty'].'"  onKeyUp="calcrow('.$sl.')"  onkeydown="changefocus(event,$(this))" onkeypress="return isFloat(event)"  required  autocomplete="off"></td>
                          <td><input type="text" name="rate[]" id="rate_'.$sl.'" class="form-control grid_table" value="'.$data['rate'].'" onKeyUp="calcrow('.$sl.')" onkeypress="isFloat(event)" onkeydown="changefocus(event,$(this))" ></td>
                          <td><input type="text" name="disamt[]" id="disamt_'.$sl.'" class="form-control grid_table" value="'.$data['disamt'].'" onKeyUp="calcrow('.$sl.')" onkeypress="isFloat(event)" onkeydown="changefocus(event,$(this))" ></td>
                           <td><input type="text" name="disper[]" id="disper_'.$sl.'" class="form-control grid_table" value="'.$data['disper'].'" onKeyUp="calcrow('.$sl.')" onkeypress="isFloat(event)" onkeydown="changefocus(event,$(this))" ></td> 
                      <td>
            <input name="amount[]" id="amount_'.$sl.'" class="form-control grid_table" value="'.$data['amount'].'" readonly="">
            <input type="hidden" name="calrow_id[]" id="calrow_id_'.$sl.'" value="'.$sl.'">
            <input type="hidden" name="particularsid[]" id="particularsid_'.$sl.'" value="'.$data['particulars_id'].'">
            <input type="hidden" name="chargesid[]" id="chargesid_'.$sl.'" value="'.$data['charge_id'].'">
                     </td>
                         </tr>';
                         $sl++;
              }
            }
             
          
            
          
          if($getmasterdata)
          {
             echo json_encode(array('msg'=>$getmasterdata,'error' =>'','error_message' =>'','getchilddata'=> $html));
          }
          else
          {
              echo json_encode(array('msg'=>'','error' =>'Name data Found','error_message' =>''));
          }
        }
        else
        {
           echo json_encode(array('msg'=>'','error' =>'Name data Found','error_message' =>''));
        }
      }
      else
      {
           echo json_encode(array('msg'=>'','error'=> '','error_message' => $this->form_validation->error_array()));
      }
  }

  public function updatedata()
  {
      $this->form_validation->set_rules('edit_billing_master_id', 'Edit ID', 'trim|required|min_length[1]|max_length[1000]|numeric');
      $this->form_validation->set_rules('patient_registration_id', 'Select Patient', 'trim|required|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('insurance_company_id', 'Insurance Company', 'trim|min_length[1]|max_length[30]|numeric');
      $this->form_validation->set_rules('doctor_id', 'Dotors', 'trim|required|min_length[1]|max_length[30]|numeric');
      $this->form_validation->set_rules('particularsid[]', 'Particular', 'trim|required|min_length[1]|max_length[30]|numeric');
      $this->form_validation->set_rules('chargesid[]', 'Charge Type', 'trim|required|min_length[1]|max_length[30]|numeric');
      $this->form_validation->set_rules('quantity[]', 'Quantity', 'trim|required|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('rate[]', 'Rate', 'trim|required|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('disamt[]', 'Discount Amount', 'trim|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('disper[]', 'Discount Percentage', 'trim|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('amount[]', 'Amount', 'trim|min_length[1]|max_length[30]');
      $this->form_validation->set_rules('modeofpay_id', 'Modeofpay', 'trim|required|min_length[1]|max_length[30]|numeric');
      $this->form_validation->set_rules('description', 'Description', 'trim');
      if($this->form_validation->run() == TRUE)
      {
        $edit_id=trim(htmlentities($this->input->post('edit_billing_master_id')));
        $data=$this->fetch_data();
        $getresult=$this->Billing_model->updatedata($data,$edit_id);
          if($getresult)
          {
            echo json_encode(array('msg'=>'Updated Successfully','error' =>'','error_message' =>''));
          }
          else
          {
            echo json_encode(array('msg'=>'','error' =>'Failed to Update','error_message' =>''));
          }
      }
      else
      {
       echo json_encode(array('msg'=>'','error' =>'','error_message' =>$this->form_validation->error_array()));
      }
  }

  public function deletedata()
  {
    $this->form_validation->set_rules('getid', 'Delete ID', 'trim|required|min_length[1]|max_length[100]|numeric');
      if($this->form_validation->run() == TRUE)
      {
          $delete_id=trim(htmlentities($this->input->post('getid')));
          $var_array=array($delete_id,$this->session->userdata('office_id'));
          $getresult=$this->Billing_model->deletedata($delete_id);
          if($getresult)
          {
             echo json_encode(array('msg'=>'Deleted Successfully','error'=>'','error_message' =>''));
          }
          else
          {
            echo json_encode(array('msg'=>'','error'=>'Failed to Delete','error_message' =>''));
          }
        }
        else
        {
              echo json_encode(array('msg'=> '', 'error'=> 'Delete ID Not Found','error_message' =>''));
        }
     
  }
	public function print_billing() 
  {
      $this->Billing_model->print_bill($this->input->post('billing_id'),$this->session->userdata('office_id'));
  }

}
