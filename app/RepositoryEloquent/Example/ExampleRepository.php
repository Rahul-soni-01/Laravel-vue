<?php
namespace App\RepositoryEloquent\Example;

use App\Models\User;
use App\RepositoryEloquent\BaseRepository;

class ExampleRepository extends BaseRepository implements ExampleInterface
{
    public function model()
    {
        return User::class;
    }

    public function example()
    {
        return "data";
    }
}
