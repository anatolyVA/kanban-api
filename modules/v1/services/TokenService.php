<?php

namespace app\modules\v1\services;

use app\common\interfaces\TokenServiceInterface;
use app\common\models\User;
use app\common\models\UserToken;
use DateTime;
use DateTimeImmutable;
use kaabar\jwt\Jwt;
use Lcobucci\JWT\Token;
use Yii;
use yii\db\Query;

class TokenService implements TokenServiceInterface
{
    private Jwt $jwt;

    public function __construct()
    {
        $this->jwt = Yii::$app->jwt;
    }

    public function generateJwt($payload, $expiresAt): string
    {
        $signer = $this->jwt->getSigner('HS256');
        $key = $this->jwt->getKey();

        $now = new DateTimeImmutable();

        $jwtParams = Yii::$app->params['jwt'];

        $token = $this->jwt->getBuilder()
            // Configures the issuer (iss claim)
            ->issuedBy($jwtParams['issuer'])
            // Configures the audience (aud claim)
            ->permittedFor($jwtParams['audience'])
            // Configures the id (jti claim)
            ->identifiedBy($jwtParams['id'], true)
            // Configures the time that the token was issue (iat claim)
            ->issuedAt($now)
            // Configures the time that the token can be used (nbf claim)
            ->canOnlyBeUsedAfter($now->modify($jwtParams['request_time']))
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($expiresAt)
            // Configures a new claim, called "uid"
            ->withClaim('id', $payload->id)
            // Builds a new token
            ->getToken($signer, $key);

        return $token->toString();
    }

    public function generateTokens($payload): array
    {
        $now = new DateTimeImmutable();
        $refreshTokenExpires = $now->modify('+30 day');
        $accessTokenExpires = $now->modify('+30 minute');

        return [
            'refresh_token' => self::generateJwt($payload, $refreshTokenExpires),
            'access_token' => self::generateJwt($payload, $accessTokenExpires),
        ];
    }

    public function validateToken(string $token): bool
    {
        $token_model = $this->jwt->loadToken($token);

        if (!$token_model instanceof Token) {
            return false;
        }
        return $this->jwt->validateToken($token_model);
    }

    public function deleteRefreshToken(string $token): bool|int
    {
        $model = UserToken::findOne(['refresh_token' => $token]);
        if (!isset($model)) {
            return false;
        }

        return $model->delete();
    }

    public function updateRefreshToken(string $token, UserToken $token_model): bool
    {
        $token_model->refresh_token = $token;
        $currentDate = new DateTime();
        $currentDate->modify('+30 days');

        $token_model->expiration_date = $currentDate->format('Y-m-d H:i:s');
        return $token_model->save();
    }

    public function createRefreshToken(string $token, User $user): bool
    {
        $userIP = Yii::$app->request->userIP;
        $token_model = new UserToken([
            'refresh_token' => $token,
            'user_id' => $user->getId(),
            'user_ip' => $userIP
        ]);

        $currentDate = new DateTime();
        $currentDate->modify('+30 days');

        $token_model->expiration_date = $currentDate->format('Y-m-d H:i:s');

        return $token_model->save();
    }

    public function deleteExpiredTokens(): void
    {
        $current_date = new DateTime();
        $expired_tokens = (new Query())
            ->from('user_token')
            ->where(['<', 'expirationDate', $current_date->format('Y-m-d H:i:s')])
            ->all();

        if (!empty($expired_tokens)) {
            foreach ($expired_tokens as $token) {
                $deletedCount = UserToken::deleteAll(['refresh_token' => $token['refresh_token']]);

                if ($deletedCount === 0) {
                    Yii::warning('Unable to delete token: ' . $token);
                }
            }
        } else {
            Yii::info('No expired tokens found.');
        }
    }
}
