<?php
    
    
    class CompaniesController extends BaseController {
    
    
    
    
        public function dashboard()
        {
            return View::make('companies.index');
        }
    
        public function edit($id)
        {
    
            $company = NULL;
            $mode = "new";
            $mangopay_user = NULL;
    
            if($id > 0)
            {
                 $mode = "edit";
                 $company = Company::find($id);
            }
            else
                $company = new Company;
    
    
            if($company->is_mangopay())
                 $mangopay_user = $this->mango_instance->get_user_by_company($company->id);
    
                
            
           return View::make('companies.company.edit', ['mode' => $mode, 'master_company' =>  $this->master_company , 'company' => $company, 'auth' => Auth::User(), 'mangopay_user' => $mangopay_user]);
    
        }
    
        public function save()
        {
            $status = new Status(TRUE, 'success', 'Company Saved');
    
            $company = NULL;
    
            if(isset($_POST["company"]))
                $company_data = json_decode($_POST["company"]);
            else
            {
    
                  $status->result = FALSE;
                  $status->status='An with the data sent to server, please try again later';
                  $status->msg = $ex;
                  return json_encode($status);
    
            }
    
    
            try
            {
    
                $master = FALSE;
    
                if($company_data->id > 0)
                {
    
                    $company = Company::find($company_data->id);
                    
                    if($company->type == 'master')
                        $master = TRUE;
                }
                else
                    $company = new Company;
    
                $company->fill((array)$company_data);
    
                if($master)
                    $company->type = 'master';
                else
                    $company->type = 'client';
    
    
    
                $company->save();
    
                if (Input::hasFile('img'))
                {
    
                      $file = Input::file('file');
                      $destinationPath = 'acdp/public/img/companies/';
                      $db_path= 'img/companies/'.$company->id.'.png';
                      // If the uploads fail due to file system, you can try doing public_path().'/uploads' 
    
                      $filename = $company->id.'.png';
                      $upload_success = Input::file('img')->move($destinationPath, $filename);
    
                      $company->img = $db_path;
                      $company->save();
    
                 }
    
                 $status->msg='Company '.$company->name. ' saved with success';
             }
             catch(Exception  $ex)
             {
    
                //$status->result = FALSE;
                //$status->status='An error ocurred when saving company';
                //$status->msg = $ex;
             }
    
             echo json_encode($status);
        }
    
    
    
        public function index()
        {
            $this->JTable(Company::all());
        }
    
    
        public function companies()
        {
            $data = Company::all();
            echo json_encode($data);
    
        }
    
        public function drivers($id)
        {
            $data = Company::find($id);
            echo json_encode($data->drivers);
    
        }
    }
