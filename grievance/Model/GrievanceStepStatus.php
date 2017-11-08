<?php

class GrievanceStepStatus implements JsonSerializable {

    private $mStep;
    private $mStatus;
    private $mMandatory;
    private $mCloseStep;
    private $mPromoteStep;
    private $mRequiredData;
    private $mStatusOrder;

    public function __construct($step, $status, $mandatory, $closeStep, $promoteStep, $requierData, $statusOrder) {
        $this->mStep = $step;
        $this->mStatus = $status;
        $this->mMandatory = $mandatory;
        $this->mCloseStep = $closeStep;
        $this->mPromoteStep = $promoteStep;
        $this->mRequiredData = $requierData;
        $this->mStatusOrder = $statusOrder;
        
    }

    function getStep() {
        return $this->mStep;
    }
    
    function getStatusOrder()
    {
        return (int)$this->mStatusOrder;
    }

    function getStatus() {
        return $this->mStatus;
    }

    function isMandatory() {
        return (bool)$this->mMandatory;
    }

    function isCloseStep() {
        return (bool)$this->mCloseStep;
    }

    function isPromoteStep() {
        return (bool)$this->mPromoteStep;
    }

    public function jsonSerialize() {
        return ['status' => $this->mStatus, 'mandatory' => (bool)$this->mMandatory, 'close_step' => (bool)$this->mCloseStep, 'promote_step' => (bool)$this->mPromoteStep, 'required_data' => $this->mRequiredData, 'status_order' => (int)$this->mStatusOrder];
    }

}
