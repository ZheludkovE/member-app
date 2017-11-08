<?php

class Member implements JsonSerializable {

    private $mId;
    private $mMemberId;
    private $mCompanyPrefix;
    private $mEmpNumber;
    private $mFirstName;
    private $mLastName;
    private $mReportTo;
    private $rol;

    public function __construct($id, $companyprfix, $empNumber, $firstName, $lastName, $rol, $reportTo, $memberId) {
        $this->mId = $id;
        $this->mMemberId = $memberId;
        $this->mCompanyPrefix = $companyprfix;
        $this->mEmpNumber = $empNumber;
        $this->mFirstName = $firstName;
        $this->mLastName = $lastName;
        $this->rol = $rol;
        $this->mReportTo = $reportTo;
    }

    function getId() {
        return $this->mId;
    }

     function getMemberId() {
        return $this->mMemberId;
    }
    
    function getFirstName() {
        return $this->mFirstName;
    }

    function getLastName() {
        return $this->mLastName;
    }

    function getCompanyPrefix() {
        return $this->mCompanyPrefix;
    }

    function getEmpNumber() {
        return $this->mEmpNumber;
    }

    function getMemberRol() {
        return $this->rol;
    }

    function getReportTo() {
        return $this->mReportTo;
    }

    public function jsonSerialize() {
        return ["id" => $this->getId(), "Member_ID" => $this->getMemberId(), "Company_Prefix" => $this->getCompanyPrefix(), "Emp_No" => $this->getEmpNumber(), "First_Name" => $this->getFirstName(), "Last_Name" => $this->getLastName()];
    }

    public function setFromJsonArray($jsonArray) {
        $settingOk = true;
        foreach ($jsonArray as $key => $value) {
            switch ($key) {
                case 'id':
                    $this->mId = $value;
                    break;
                case 'Member_ID':
                    $this->mMemberId = $value;
                    break;
                case 'Company_Prefix':
                    $this->mCompanyPrefix = $value;
                    break;
                case 'Emp_No':
                    $this->mEmpNumber = $value;
                    break;
                case 'First_Name':
                    $this->mFirstName = $value;
                    break;
                case 'Last_Name':
                    $this->mLastName = $value;
                    break;
                default:
                    break;
            }
          
        }
        return $settingOk;
    }

}
