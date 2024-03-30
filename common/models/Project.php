<?php

namespace app\common\models;

use yii\db\ActiveRecord;

/**
 * @property string $id
 * @property string $title
 * @property string $user_id
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
            [['title', 'user_id'], 'required'],
            ['title', 'string', 'min' => 6, 'max' => 32]
        ];
    }

    public static function findByCreatorId(string $id): array
    {
        return self::findAll(['user_id' => $id]);
    }

    public static function findByCreatorIdAndProjectId($project_id, $user_id): Project|null
    {
        return self::findOne(['user_id' => $user_id, 'id' => $project_id]);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
