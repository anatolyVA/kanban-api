<?php

namespace app\common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
/**
 * @property string $id
 * @property string $view_id
 * @property string $title
 * @property int $status
*/
class Collection extends ActiveRecord
{

    public static function tableName(): string
    {
        return '{{%collection}}';
    }

    public function rules(): array
    {
        return [
            [['title', 'view_id'], 'required'],
            ['title', 'string', 'min' => 3, 'max' => 32],
            ['status', 'integer', 'min' => 0, 'max' => 1],
        ];
    }

    public function getView(): ActiveQuery
    {
        return $this->hasOne(View::class, ['id' => 'view_id']);
    }

    public function getTasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['collection_id' => 'id']);
    }

    public static function findById($id): ?Collection
    {
        return self::findOne($id);
    }
}