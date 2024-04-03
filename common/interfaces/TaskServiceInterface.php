<?php

namespace app\common\interfaces;

interface TaskServiceInterface
{
    public function create(string $title, string $status, int $project_id);

    public function rename(int $id, string $title);

    public function delete(int $id);

    public function changeStatus(int $id, string $status);

}
