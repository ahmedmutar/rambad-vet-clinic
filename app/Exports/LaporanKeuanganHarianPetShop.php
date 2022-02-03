<?php

namespace App\Exports;

use App\Exports\DataLaporanKeuanganHarianPetShop;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanKeuanganHarianPetShop implements WithMultipleSheets
{
    use Exportable;

    protected $sheets;
    protected $orderby;
    protected $column;
    protected $date;
    protected $branch_id;

    public function __construct($orderby, $column, $date, $branch_id)
    {
        $this->orderby = $orderby;
        $this->column = $column;
        $this->date = $date;
        $this->branch_id = $branch_id;
    }

    function array(): array
    {
        return $this->sheets;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets = [
            new DataLaporanKeuanganHarianPetShop($this->orderby, $this->column, $this->date, $this->branch_id),
        ];

        return $sheets;
    }
}
