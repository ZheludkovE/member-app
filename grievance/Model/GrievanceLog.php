<?php

class GrievanceLog implements JsonSerializable {

    private $mGrievanceId;
    private $mId;
    private $mStep;
    private $mTitle;
    private $mDateTime;
    private $mDescription;
    private $mMember;
    private $mUser;
    private $mStatus;

    public function __construct($grievanceId, $id, $step, $status, $member, $title, $user, $dateTime, $description) {
        $this->mGrievanceId=$grievanceId;
        $this->mId =$id;
        $this->mStep = $step;
        $this->mStatus= $status;
        $this->mDateTime = $dateTime;
        $this->mMember = $member;
        $this->mTitle=$title;
        $this->mUser = $user;
        $this->mDescription=$description;
    }
 
    function getGrievanceId() {
        return $this->mGrievanceId;
    }

    function getStep() {
        return $this->mStep;
    }

    function getTitle() {
        return $this->mTitle;
    }

    function getDateTime() {
        return $this->mDateTime;
    }

    function getDescription() {
        return $this->mDescription;
    }

    function getMember() {
        return $this->mMember;
    }

    function getUser() {
        return $this->mUser;
    }

    function getStatus() {
        return $this->mStatus;
    }

                
    public function jsonSerialize() {
        return ['id' => (int)$this->mId, 'step' => $this->mStep, 'status' => $this->mStatus, 'title'=> $this->mTitle, 'date_time' => $this->mDateTime, 'member'=> $this->mMember, 'user'=> $this->mUser, 'description'=> $this->mDescription];
    }

}


