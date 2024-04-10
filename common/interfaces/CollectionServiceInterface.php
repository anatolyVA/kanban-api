<?php

namespace app\common\interfaces;

interface CollectionServiceInterface
{
    public function getAll($view_id): array;
    public function getOne($id);
    public function create($data, $view_id);
    public function update($id, $data);
    public function delete($id);
}