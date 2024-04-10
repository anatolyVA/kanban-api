<?php

namespace app\common\interfaces;

interface WorkspaceServiceInterface
{
    public function getOne(string $id);
    public function getAll();
    public function create(string $title);
    public function delete(string $id);
    public function rename(string $id, string $title);
    public function invite(string $id, array $user_ids);
    public function exclude(string $id, array $user_ids);
    public function exit(string $id);
}