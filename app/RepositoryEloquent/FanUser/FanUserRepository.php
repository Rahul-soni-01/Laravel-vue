<?php
namespace App\RepositoryEloquent\FanUser;

use App\Models\FanUser;
use App\RepositoryEloquent\BaseRepository;

class FanUserRepository extends BaseRepository implements FanUserInterface
{
    public function model()
    {
        return FanUser::class;
    }
}
