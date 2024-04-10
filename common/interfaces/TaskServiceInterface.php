<?php

namespace app\common\interfaces;

interface TaskServiceInterface
{
    public function getAll(string $collection_id);
    public function getOne(string $id);
    public function create($data, string $collection_id);

    public function update(string $id, $data);

    public function delete(string $id);

}
