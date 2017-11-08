<?php

class User implements JsonSerializable {

    private $mId;
    private $mFirstName;
    private $mLastName;
    private $mRole;
    private $mUserRole;
    private $mReportTo;
    private $mEmail;

    public function __construct($id, $firstName, $lastName, $userRole, $reportTo, $role, $email) {
        $this->mId = $id;
        $this->mFirstName = $firstName;
        $this->mLastName = $lastName;
        $this->mReportTo = $reportTo;
        $this->mUserRole=  $userRole;
        $this->mRole = $role;
        $this->mEmail=$email;
        
    }

    function getId() {
        return $this->mId;
    }

    function getFirstName() {
        return $this->mFirstName;
    }

    function getLastName() {
        return $this->mLastName;
    }


    function getReportTo() {
        return $this->mReportTo;
    }
    
    function getRole() {
        return $this->mRole;
    }
    
    function getUserRole() {
        return $this->mUserRole;
    }
    
    function getEmail() {
        return $this->mEmail;
    }

    public function jsonSerialize() {
        return ["id"=> $this->getId(), "fname" => $this->getFirstName(), "lname" => $this->getLastName()];
    }

}