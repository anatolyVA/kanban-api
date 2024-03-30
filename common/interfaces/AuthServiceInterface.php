<?php

namespace app\common\interfaces;

interface AuthServiceInterface
{
    public function register($data);
    public function login(string $email, string $password);
    public function logout(string $refresh_token);
    public function refresh(string $refresh_token);
}
