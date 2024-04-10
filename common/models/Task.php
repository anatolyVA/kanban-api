<?php

namespace app\common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $id
 * @property string $title
 * @property string $collection_id
 * @property string $creator_id
 * @property string $description
 * @property string $is_completed
 * @property string $deadline
 * @property int $priority
 * @property string $parent_id
 * */
class Task extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%task}}';
    }

    public static function findById(string $id): ?Task
    {
        return static::findOne($id);
    }

    public function rules(): array
    {
        return [
            [['title', 'collection_id', 'creator_id'], 'required'],
            ['title', 'string', 'min' => 3, 'max' => 32],
            ['priority', 'integer', 'min' => 0, 'max' => 1],
            ['is_completed', 'boolean'],
            ['description', 'string'],
            ['parent_id', 'safe'],
        ];
    }

    public function getCollection(): ActiveQuery
    {
        return $this->hasOne(Collection::class, ['id' => 'collection_id']);
    }

    public function getSubtasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['parent_id' => 'id']);
    }

    public function getComments(): ActiveQuery
    {
        return $this->hasMany(Comment::class, ['task_id' => 'id']);
    }

}
