<?php

namespace App\Service;

class Helpers
{
    private $langue;
    public function __construct($langue){
        $this->langue = $langue;
    }

    public function sayCc(): string {
        return 'cc';
    }
}