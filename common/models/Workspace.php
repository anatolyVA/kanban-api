<?php

namespace app\common\models;

use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property string $id
 * @property string $title
 * @property string $creator_id
 * @property array $members
 */
class Workspace extends ActiveRecord
{

    public static function tableName(): string
    {
        return "{{%workspace}}";
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['title', 'creator_id'], 'required'],
            ['title', 'string', 'min' => 3, 'max' => 32]
        ];
    }

    public static function findByCreatorId(string $creator_id): array
    {
        $workspaces = self::find()
            ->with('members')
            ->where(['creator_id' => $creator_id])
            ->all();

        return ArrayHelper::toArray($workspaces, [
            self::class => [
                'id',
                'title',
                'creator_id',
                'members',
                'projects'
            ],
        ]);
    }

    public static function findByUserId(string $user_id): array
    {
        $user = User::findIdentity($user_id);
        return ArrayHelper::toArray($user->workspaces, [
            self::class => [
                'id',
                'title',
                'creator_id',
                'members',
                'projects'
            ],
        ]);
    }

    public static function findById(string $workspace_id): ?Workspace
    {
        return self::findOne($workspace_id);
    }


    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @throws InvalidConfigException
     */
    public function getMembers(): ActiveQuery
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('workspace_user', ['workspace_id' => 'id']);
    }
    public function getProjects(): ActiveQuery
    {
        return $this->hasMany(Project::class, ['workspace_id' => 'id']);
    }

    /**
     * @throws InvalidConfigException
     */
    public function isMember(string $user_id): bool
    {
        return boolval($this->getMembers()->where(['id' => $user_id])->one());
    }
}
