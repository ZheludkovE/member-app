<?php

class GrievanceAlerts implements JsonSerializable {

    private $mFiveDay;
    private $mNewAssignment;

    public function __construct($newAssignment, $fiveDays) {
        $this->mFiveDay = $fiveDays;
        $this->mNewAssignment = $newAssignment;
    }

    public function jsonSerialize() {
        return ['new_assignment' => $this->mNewAssignment, 'five_days_to_complete' => $this->mFiveDay];
    }
    
}