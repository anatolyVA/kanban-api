<?php

namespace app\common\interfaces;

interface ProjectServiceInterface
{
    public function create(string $title, string $workspace_id);
    public function delete(string $id);
    public function rename(string $id, string $title);
    public function getOne(string $id);
    public function getAll(string $workspace_id);

}