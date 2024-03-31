<?php

namespace app\common\models;

use kaabar\jwt\Jwt;
use Lcobucci\JWT\Token;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $role
 * @property string $password
 */
class User extends ActiveRecord implements IdentityInterface
{
    public function fields(): array
    {
        $fields = parent::fields();

        unset(
            $fields['password'],
        );

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['first_name', 'password', 'last_name', 'email', 'role'], 'required'],
            [['first_name', 'last_name'], 'string', 'min' => 2, 'max' => 52],
            ['email', 'email'],
            ['password', 'string', 'min' => 6, 'max' => 255]
        ];
    }

    public const SCENARIO_LOGIN = 'login';
    public const SCENARIO_REGISTER = 'register';

    public const SCENARIO_DEFAULT = 'default';

    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_LOGIN] = ['email', 'password'];
        $scenarios[self::SCENARIO_REGISTER] = ['first_name', 'last_name', 'role', 'email', 'password'];
        return $scenarios;
    }

    public static function tableName(): string
    {
        return "{{%user}}";
    }

    /** Finds an identity by username
     * @param $email
     * @return User|null
     */
    public static function findByEmail($email): ?User
    {
        return static::findOne(['email' => $email]);
    }


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): User|null
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null): array|ActiveRecord|IdentityInterface|null
    {
        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;

        $token_model = $jwt->loadToken($token);

        if (!$token_model instanceof Token) {
            return null;
        }

        return static::find()
            ->where(['id' => $token_model->claims()->get("id")])
            ->one();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
    }

    public function validatePassword(string $password): bool
    {
        return \Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

    /**
     * @throws Exception
     */
    public function hashPassword(string $password): void
    {
        $this->password = \Yii::$app->getSecurity()->generatePasswordHash($password);
    }

    public function getProjects(): array
    {
        return $this->hasMany(Project::class, ['id' => 'project_id'])
            ->via('project_user')->all();
    }
}
