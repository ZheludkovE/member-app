<?php

class GrievanceInfoRequestOptions implements JsonSerializable {

    private $mFirstPart;
    private $mSecondPart;

    public function __construct($firstPart, $secondPart) {
        $this->mFirstPart = $firstPart;
        $this->mSecondPart = $secondPart;
    }
    
    public function getFirst()
    {
        return $this->mFirstPart;
    }
    
    public function getSecond()
    {
        return $this->mSecondPart;
    }

    public function jsonSerialize() {
        return ['first_part' => $this->mFirstPart, 'second_part' => $this->mSecondPart];
    }
    
}