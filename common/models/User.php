<?php

namespace app\common\models;

use kaabar\jwt\Jwt;
use Lcobucci\JWT\Token;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $role
 * @property string $password
 * @property array $workspaces
 */
class User extends ActiveRecord implements IdentityInterface
{

    public function fields(): array
    {
        $fields = parent::fields();

        unset(
            $fields['password'],
            $fields['email'],
        );

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['first_name', 'password', 'last_name', 'email', 'username'], 'required'],
            [['first_name', 'last_name'], 'string', 'min' => 2, 'max' => 52],
            ['email', 'email'],
            ['username', 'string', 'min' => 2, 'max' => 16],
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
        $scenarios[self::SCENARIO_REGISTER] = ['first_name', 'last_name', 'email', 'username', 'password'];
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
    public static function findByUsername($username): ?User
    {
        return static::findOne(['username' => $username]);
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

    /**
     * @throws InvalidConfigException
     */
    public function getWorkspaces(): ActiveQuery
    {
        return $this->hasMany(Workspace::class, ['id' => 'workspace_id'])
            ->viaTable('workspace_user', ['user_id' => 'id']);
    }

    public function getTasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['creator_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getInvitations(): ActiveQuery
    {
        return $this->hasMany(Workspace::class, ['id' => 'workspace_id'])
            ->viaTable('workspace_invitation', ['user_id' => 'id']);
    }
}
