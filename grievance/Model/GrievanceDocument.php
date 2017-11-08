<?php

class GrievanceDocument implements JsonSerializable {

   
    private $mDateTime;
    private $mName;
    private $mId;
    private $mMember;
    private $mFileSize;
    private $mUser;
    
    private $mPath;
 

    public function __construct($id, $fileName, $fileSize, $dateTime, $member, $user, $path) {
        $this->mName=$fileName;
        $this->mDateTime = $dateTime;
        $this->mFileSize =$fileSize;
        $this->mId = $id;
        $this->mMember = $member;
        $this->mUser = $user;
        $this->mPath = $path;
        
    }

    function getDateTime() {
        return $this->mDateTime;
    }

    function getName() {
        return $this->mName;
    }

    function getId() {
        return $this->mId;
    }

    function getMember() {
        return $this->mMember;
    }

    function getFileSize() {
        return $this->mFileSize;
    }
    function getUser() {
        return $this->mUser;
    }
    function getPath()
    {
       return $this->mPath . rand(1000,9999).base64_encode($this->mId);
    }
    
        
    public function jsonSerialize() {
        return ['id'=> $this->mId, 'file_name' => $this->mName, 'file_size' => round($this->mFileSize/1024,1) ." KB", 'date' => date("M d, Y H:i:s O", strtotime($this->mDateTime)), 'member'=> $this->mMember , 'user'=>$this->mUser, 'path' => $this->getPath()];
    }
    

}

