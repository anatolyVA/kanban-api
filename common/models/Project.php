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
 * @property int $membersCount
 * @property array $members
 */
class Project extends ActiveRecord
{
    public int $membersCount;

    public static function tableName(): string
    {
        return "{{%project}}";
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
        $projects = self::find()
            ->with('members')
            ->where(['creator_id' => $creator_id])
            ->all();

        return ArrayHelper::toArray($projects, [
            self::class => [
                'id',
                'title',
                'creator_id',
                'members',
            ],
        ]);
    }

    public static function findByUserId(string $user_id): array
    {
        $user = User::findIdentity($user_id);
        return ArrayHelper::toArray($user->projects, [
            self::class => [
                'id',
                'title',
                'creator_id',
                'members',
            ],
        ]);
    }

    public static function findById($project_id): ?Project
    {
        $project = self::findOne($project_id);

        if ($project == null) {
            return null;
        }

        return $project;
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
            ->viaTable('project_user', ['project_id' => 'id']);
    }

    /**
     * @throws InvalidConfigException
     */
    public function isMember(string $user_id): bool
    {
        return boolval($this->getMembers()->where(['id' => $user_id])->one());
    }
}
