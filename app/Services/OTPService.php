<?php

namespace App\Services;

use PragmaRX\Google2FA\Google2FA;

class OTPService
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateOTP()
    {
        return $this->google2fa->generateSecretKey();
    }

    public function verifyOTP($secret, $code)
    {
        return $this->google2fa->verifyKey($secret, $code);
    }
}
