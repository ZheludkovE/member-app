<?php

class GrievanceStepRelation implements JsonSerializable {

    private $mDateOfGrivanceMeeting;
    private $mNameOfDesignee;
    private $mCompanyRep;
    private $mOtherPresent;
    private $mInfoRequestedInWritingByDesignee;
    private $mStep;
    private $mStatus;
    private $mInfoProvidedByCompany;
    private $mCompanyProvideWrittenResponse;
    private $mWrittenResponseDate;
    private $mSteward;
    private $mUser;

    public function __construct($step, $status, $steward, $user, $dateOfGrievanceMetting, $nameOfDesignee, $companyRep, $otherPresent, $infoRequestedInWritingByDesignee, $infoProvidedByCompany, $companyProvideWrittenResonse, $writtenResponseDate) {
        $this->mStep = $step;

        $this->mDateOfGrivanceMeeting = $dateOfGrievanceMetting;
        $this->mNameOfDesignee = $nameOfDesignee;
        $this->mCompanyRep = $companyRep;
        $this->mOtherPresent = $otherPresent;
        $this->mInfoRequestedInWritingByDesignee = $infoRequestedInWritingByDesignee;
        $this->mInfoProvidedByCompany = $infoProvidedByCompany;
        $this->mCompanyProvideWrittenResponse = $companyProvideWrittenResonse;
        $this->mStatus = $status;
        $this->mSteward = $steward;
        $this->mUser = $user;
        $this->mWrittenResponseDate= $writtenResponseDate;
    }

    function getDateOfGrievanceMeeting() {
        return $this->mDateOfGrivanceMeeting;
    }

    function getNameOfDesignee() {
        return $this->mNameOfDesignee;
    }

    function getStatus() {
        return $this->mStatus;
    }

    function getStep() {
        return $this->mStep;
    }

    function getCompanyProvideWrittenResponse() {
        return $this->mCompanyProvideWrittenResponse;
    }

    function getInfoProvidedByCompany() {
        return $this->mInfoProvidedByCompany;
    }

    function getInfoRequestedInWritingByDesignee() {
        return $this->mInfoRequestedInWritingByDesignee;
    }
    
    function getWrittenResponseDate() {
        return $this->mWrittenResponseDate;
    }

    function getOtherPresent() {
        return $this->mOtherPresent;
    }

    function getCompanyRep() {
        return $this->mCompanyRep;
    }
    
    function getSteward() {
        return $this->mSteward;
    }
    function getUser() {
        return $this->mUser;
    }

    function setStep($step) {
        $this->mStep = $step;
    }

    function setStatus($status) {
        $this->mStatus = $status;
    }
    
    function setSteward($steward) {
        $this->mSteward = $steward;
    }

    function setUser($user) {
        $this->mUser = $user;
    }
    

    public function jsonSerialize() {
        return ['step' => $this->mStep,
            'date_of_grievance_meeting' =>(!empty($this->mDateOfGrivanceMeeting)?date("M d, Y H:i:s O", strtotime($this->mDateOfGrivanceMeeting)): $this->mDateOfGrivanceMeeting),
            'name_of_designee' => $this->mNameOfDesignee,
            'name_of_company_representative' => $this->mCompanyRep,
            'name_of_others_present' => $this->mOtherPresent,
            'was_information_requested_in_writing_by_designee' => (bool) $this->mInfoRequestedInWritingByDesignee,
            'was_information_provided_by_company_rep' => (bool) $this->mInfoProvidedByCompany,
            'did_company_rep_provide_written_response' => (bool) $this->mCompanyProvideWrittenResponse,
            'written_response_date'=> (!empty($this->mWrittenResponseDate)?date("M d, Y H:i:s O", strtotime($this->mWrittenResponseDate)): $this->mWrittenResponseDate),
            'status' => $this->mStatus,
            'steward'=> $this->mSteward,
            'user' => $this->mUser];   
    }

      public function setFromJsonArray($jsonArray) {
        $settingOk = true;
        foreach ($jsonArray as $key => $value) {
     
            switch ($key) {
                case 'step':
                    $step = new GrievanceStep();
                    if ($step->setFromJsonArray($value)) {
                        $this->mStep = $step;
                    } 
                    break;
                case 'date_of_grievance_meeting':
                    if($value!=null)
                    {
                    $dateOfGrievance = strtotime($value);
                    $this->mDateOfGrivanceMeeting = date("M d, Y H:i:s", $dateOfGrievance);
                    }
                    break;
                case 'name_of_designee':
                    $this->mNameOfDesignee = $value;
                    break;
                case 'name_of_company_representative':
                    $this->mCompanyRep = $value;
                    break;
                case 'name_of_others_present':
                    $this->mOtherPresent = $value;
                    break;
                case 'was_information_requested_in_writing_by_designee':
                    $this->mInfoRequestedInWritingByDesignee = $value;
                    break;
                case 'was_information_provided_by_company_rep':
                    $this->mInfoProvidedByCompany = $value;
                    break;
                case 'did_company_rep_provide_written_response':                       
                     $this->mCompanyProvideWrittenResponse = $value;
                    break;
                case 'written_response_date':
                    if (!empty($value))
                    {
                        $dateOfResponseDate = strtotime($value);
                        $this->mWrittenResponseDate = date("M d, Y H:i:s", $dateOfResponseDate);
                    }
                    break;
                case 'status':
                    $status = new GrievanceStatus();
                    if ($status->setFromJsonArray($value)) {
                        $this->mStatus = $status;
                    } 
                    break;
                default:

                    break;
            }
           
        }

 
        $settingOk = (!$this->mCompanyProvideWrittenResponse) || ($this->mCompanyProvideWrittenResponse && !empty($this->mWrittenResponseDate));
        return $settingOk;
    }
}