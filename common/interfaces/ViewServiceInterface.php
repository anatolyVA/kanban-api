<?php

namespace app\common\interfaces;

interface ViewServiceInterface
{
    public function create(string $title, string $project_id);
    public function delete(string $id);
    public function rename(string $id, string $title);
    public function getOne(string $id);
    public function getAll(string $project_id);
    public function addMembers(string $id, array $user_ids);
    public function excludeMembers(string $id, array $user_ids);
    public function getMembers(string $id);
}