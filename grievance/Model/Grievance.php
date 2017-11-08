<?php

class Grievance implements JsonSerializable {

    private $mId;
    private $mMember;
    private $mClauseOfContractViolated;
    private $mAddress1;
    private $mAddress2;
    private $mBureau;
    private $mCity;
    private $mDepartment;
    private $mEmail;
    private $mPhone;
    private $mState;
    private $mSupervisor;
    private $mTitle;
    private $mWorkLocation;
    private $mZipCode;
    private $mGrievanceNature;
    private $mNatureOfGrievanceOther;
    private $mOriginalDateOfGrievance;
    private $mGrievanceRemedy;
    private $mRemedyOther;
    private $mSteps;
    private $mSteward;
    private $mSubmissionDate;
    private $mControlDate;
    private $mSignatureFile;

    public function __construct($id = 0, $member, $clauseOfContractViolated, $address1, $address2, $bureau, $city, $departament, $email, $phone, $state, $supervisor, $title, $workLocation, $zipCode, $grievanceNature, $natureOfGrievaceOther, $origianlDateOfGrievance, $grievanceRemedy, $remedyOther, $steps, $steward, $controlDate, $submissionDate, $signatureFile) {
        $this->mId = $id;
        $this->mMember = $member;
        $this->mClauseOfContractViolated = $clauseOfContractViolated;
        $this->mAddress1 = $address1;
        $this->mAddress2 = $address2;
        $this->mBureau = $bureau;
        $this->mCity = $city;
        $this->mDepartment = $departament;
        $this->mEmail = $email;
        $this->mPhone = $phone;
        $this->mState = $state;
        $this->mSupervisor = $supervisor;
        $this->mTitle = $title;
        $this->mWorkLocation = $workLocation;
        $this->mZipCode = $zipCode;
        $this->mGrievanceNature = $grievanceNature;
        $this->mNatureOfGrievanceOther = $natureOfGrievaceOther;
        $this->mOriginalDateOfGrievance = $origianlDateOfGrievance;
        $this->mGrievanceRemedy = $grievanceRemedy;
        $this->mRemedyOther = $remedyOther;
        $this->mSteps = $steps;
        $this->mSteward = $steward;
        $this->mControlDate = $controlDate;
        $this->mSubmissionDate = $submissionDate;
        $this->mSignatureFile = $signatureFile;
    }

    public function jsonSerialize() {
        return ['member'=> $this->mMember,
            'clause_of_contract_violated' => $this->mClauseOfContractViolated,
            'member_address_1' => $this->mAddress1,
            'member_address_2' => $this->mAddress2,
            'member_bureau' => $this->mBureau,
            'member_city' => $this->mCity,
            'member_department' => $this->mDepartment,
            'member_email' => $this->mEmail,
            'member_phone' => $this->mPhone,
            'member_state' => $this->mState,
            'member_supervisor' => $this->mSupervisor,
            'member_title' => $this->mTitle,
            'member_work_location' => $this->mWorkLocation,
            'member_zip_code' => $this->mZipCode,
            'nature_of_grievance' => $this->mGrievanceNature,
            'nature_of_grievance_other' => $this->mNatureOfGrievanceOther,
            'original_date_of_grievance' => date("M d, Y H:i:s O", strtotime($this->mOriginalDateOfGrievance)),
            'remedy' => $this->mGrievanceRemedy,
            'remedy_other' => $this->mRemedyOther,
            'steps' => $this->mSteps,
            'steward' => $this->mSteward,
            'control_date'=>(!empty($this->mControlDate)? date("M d, Y H:i:s O", strtotime($this->mControlDate)) : $this->mControlDate),
            'submission_date' => date("M d, Y H:i:s O", strtotime($this->mSubmissionDate)),
            'id' => $this->mId ];
    }


    public function setFromJsonArray($jsonArray) {
        $settingOk = true;
        $this->mGrievanceNature =null;
        $this->mGrievanceRemedy =null;
        $this->mNatureOfGrievanceOther = "";
        $this->mRemedyOther = "";
        foreach ($jsonArray as $key => $value) {
            switch ($key) {
                case 'member':
                    /*If need read de member from de json uncoment the following lines
                    $member = new Member();
                    if ($member->setFromJsonArray($value)) {
                        $this->mMember = $member;
                    } else {
                        $settingOk = false;
                    }*/
                    break;
                case 'clause_of_contract_violated':
                    $this->mClauseOfContractViolated = $value;
                    break;
                case 'member_address_1':
                    $this->mAddress1 = $value;
                    break;
                case 'member_address_2':
                    $this->mAddress2 = $value;
                    break;
                case 'member_bureau':
                    $this->mBureau = $value;
                    break;
                case 'member_city':
                    $this->mCity = $value;
                    break;
                case 'member_department':
                    $this->mDepartment = $value;
                    break;
                case 'member_email':
                    $this->mEmail = $value;
                    break;
                case 'member_phone':
                    $this->mPhone = $value;
                    break;
                case 'member_state':
                    $this->mState = $value;
                    break;
                case 'member_supervisor':
                    $this->mSupervisor = $value;
                    break;
                case 'member_title':
                    $this->mTitle = $value;
                    break;
                case 'member_work_location':
                    $this->mWorkLocation = $value;
                    break;
                case 'member_zip_code':
                    $this->mZipCode = $value;
                    break;
                case 'nature_of_grievance':
                    $grievanceNat = new GrievanceNature();
                    if ($grievanceNat->setFromJsonArray($value)) {
                        $this->mGrievanceNature = $grievanceNat;
                    }
                    break;
                case 'nature_of_grievance_other':
                    $this->mNatureOfGrievanceOther = $value;
                    break;
                case 'original_date_of_grievance':
                    $originalDate = strtotime($value);
                    $this->mOriginalDateOfGrievance = date("M d, Y H:i:s", $originalDate);
                    break;
                case 'remedy':
                    $grievRemdy = new GrievanceRemedy();
                    if ($grievRemdy->setFromJsonArray($value)) { 
                        $this->mGrievanceRemedy = $grievRemdy;
                    } 
                    break;
                case 'remedy_other':
                    $this->mRemedyOther = $value;
                    break;
                case 'steps':
                    $this->mSteps = array();
                    break;
                case 'steward':
                    $steward = new Member();
                    if ($steward->setFromJsonArray($value)) {
                        $this->mSteward = $steward;
                    } 
                    break;
                case 'control_date':
//                    $controlDate = strtotime($value);
//                    $this->mControlDate = date("M d, Y H:i:s", $controlDate);
                    break;
                case 'submission_date':
                    $submissionDate = strtotime($value);
                    $this->mSubmissionDate = date("M d, Y H:i:s", $submissionDate);
                    break;
                case 'id':
                    $this->mId = (int) $value;
                    break;
                default:
                    break;
            }
           
        }

        return $settingOk;
    }

    function setId($id) {
        $this->mId = (int) $id;
    }



    function setSubmissionDate($submissionDate) {
        $this->mSubmissionDate = $submissionDate;
    }

    function setSteward($steward) {
        $this->mSteward = $steward;
    }

    function setGrievanceNature($nature) {
        $this->mGrievanceNature = $nature;
    }

    function setGrievanceRemedy($remedy) {
        $this->mGrievanceRemedy = $remedy;
    }
    
    
    function setSignatureFile($signatureFile) {
        $this->mSignatureFile = $signatureFile;
    }

    function getId() {
        return $this->mId;
    }

    function getMember() {
        return $this->mMember;
    }
    
    function setMember($mebmer) {
        $this->mMember = $mebmer;
    }

    function getClauseOfContractViolated() {
        return $this->mClauseOfContractViolated;
    }
    
    function setClauseOfContractViolated($value) {
        $this->mClauseOfContractViolated = $value;
    }

    function getAddress1() {
        return $this->mAddress1;
    }
    
     function setAddress1($value) {
         $this->mAddress1=$value;
    }

    function getAddress2() {
        return $this->mAddress2;
    }
    function setAddress2($value) {
         $this->mAddress2=$value;
    }


    function getBureau() {
        return $this->mBureau;
    }
    function setBureau($value) {
         $this->mBureau = $value;
    }

    function getCity() {
        return $this->mCity;
    }
    function setCity($value) {
        $this->mCity = $value;
    }

    function getDepartment() {
        return $this->mDepartment;
    }
    
    function setDepartment($value) {
       $this->mDepartment = $value;
    }

    function getEmail() {
        return $this->mEmail;
    }
    
    
    function setEmail($value) {
       $this->mEmail=$value;
    }

    function getPhone() {
        return $this->mPhone;
    }

    function setPhone($value) {
       $this->mPhone=$value;
    }
    
    function getState() {
        return $this->mState;
    }
    
    function setState($value) {
        $this->mState= $value;
    }

    function getSupervisor() {
        return $this->mSupervisor;
    }
    
    function setSupervisor($value) {
        $this->mSupervisor = $value;
    }

    function getTitle() {
        return $this->mTitle;
    }
    
    function setTitle($value) {
        $this->mTitle=$value;
    }

    function getWorkLocation() {
        return $this->mWorkLocation;
    }
    
    function setWorkLocation($value) {
        $this->mWorkLocation = $value;
    }

    function getZipCode() {
        return $this->mZipCode;
    }

    function setZipCode($value) {
         $this->mZipCode= $value;
    }

    function getGrievanceNature() {
        return $this->mGrievanceNature;
    }
    
    function setNatureOfGrivanceOther($value) {
        $this->mNatureOfGrievanceOther = $value;
    }


    function getNatureOfGrivanceOther() {
        return $this->mNatureOfGrievanceOther;
    }

    function getOriginalDateOfGrievance() {
        return $this->mOriginalDateOfGrievance;
    }

    function getGrievanceRemedy() {
        return $this->mGrievanceRemedy;
    }

    function getRemedyOther() {
        return $this->mRemedyOther;
    }

    function setRemedyOther($value) {
        $this->mRemedyOther=$value;
    }
    
    function getSteps() {
        return $this->mSteps;
    }

    function getSteward() {
        return $this->mSteward;
    }

    function getSubmissionDate() {
        return $this->mSubmissionDate;
    }
    
    function getControlDate()
    {
        return $this->mControlDate;
    }
    
    function getSignatureFile()
    {
        return $this->mSignatureFile;
    }
    
    function setControlDate($dateTime)
    {
        $this->mControlDate= $dateTime;
    }

}
