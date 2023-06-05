<?php

namespace App\Define;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HttpResponseViewMore
{
    /**
     * @var integer
     */
    public $total;

    /**
     * @var Collection
     */
    public $list;

    /**
     * @var string
     */
    public $lessThanColumn;

    /**
     * HttpResponseViewMore constructor.
     * @param string $lessThanColumn
     */
    public function __construct(string $lessThanColumn = "id")
    {
        $this->total = 0;
        $this->list = collect([]);
        $this->lessThanColumn = $lessThanColumn;
    }

    /**
     * Builderの設定とその結果の登録
     * @param Builder $builder
     * @param string $serializerClassName
     * @param int $perPage
     * @return $this
     */
    public function setBuilder(Builder $builder, string $serializerClassName, int $perPage)
    {
        $lessThan = null;

        $checkLessThan = request()->query("less_than");
        if ($checkLessThan && is_numeric($checkLessThan)) {
            $lessThan = (int) $checkLessThan;
        }

        // 対象データ取得用のqueryの設定
        $builder->orderBy($this->lessThanColumn, "desc")
            ->when($lessThan, function (Builder $builder, int $lessThan) {
                $builder->where($this->lessThanColumn, "<", $lessThan);
            });

        $this->total = $builder->count();

        if ($this->total > 0) {
            $list = $builder->limit($perPage)->get();
            $this->list = \App::make($serializerClassName)->makes($list);
        }

        return $this;
    }
}
