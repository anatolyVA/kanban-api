<?php

namespace app\common\interfaces;

use app\common\models\User;
use app\common\models\UserToken;
use yii\base\Model;
use yii\db\ActiveRecord;

interface TokenServiceInterface
{
    public function generateJwt($payload, $expiresAt);
    public function generateTokens($payload);
    public function validateToken(string $token);
    public function deleteRefreshToken(string $token);
    public function updateRefreshToken(string $token, UserToken $token_model);
    public function createRefreshToken(string $token, User $user);
    public function deleteExpiredTokens();
}