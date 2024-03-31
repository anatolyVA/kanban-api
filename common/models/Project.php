<?php

namespace app\common\models;

use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property string $id
 * @property string $title
 * @property string $creator_id
 */
class Project extends ActiveRecord
{
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
            ['title', 'string', 'min' => 6, 'max' => 32]
        ];
    }

    public static function findByCreatorId(string $id): array
    {
        $projects = self::find()
            ->with('members')
            ->where(['creator_id' => $id])
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

    public static function findByCreatorIdAndProjectId($project_id, $creator_id): Project|null
    {
        return self::findOne(['creator_id' => $creator_id, 'id' => $project_id]);
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @throws InvalidConfigException
     */
    public function getMembers(): \yii\db\ActiveQuery
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('project_user', ['project_id' => 'id']);
    }
}
