<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DataLaporanKeuanganBulananPetShop implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle, WithMapping
{
    protected $orderby;
    protected $column;
    protected $month;
    protected $year;
    protected $branch_id;

    public function __construct($orderby, $column, $month, $year, $branch_id)
    {
        $this->orderby = $orderby;
        $this->column = $column;
        $this->month = $month;
        $this->year = $year;
        $this->branch_id = $branch_id;
    }
    public function collection()
    {
        $item = DB::table('petshops as py')
            ->join('master_petshops as mp', 'py.master_petshop_id', '=', 'mp.id')
            ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
            ->join('price_items as pi', 'loi.id', '=', 'pi.list_of_items_id')
            ->join('category_item as ci', 'loi.category_item_id', '=', 'ci.id')
            ->join('users', 'py.user_id', '=', 'users.id')
            ->join('branches', 'mp.branch_id', '=', 'branches.id')
            ->select(
                DB::raw("DATE_FORMAT(py.created_at, '%d/%m/%Y') as created_at"),
                'mp.payment_number',
                'loi.item_name',
                'ci.category_name as category',
                'py.total_item',
                DB::raw("TRIM(pi.capital_price * py.total_item)+0 as capital_price"),
                DB::raw("TRIM(pi.selling_price * py.total_item)+0 as selling_price"),
                DB::raw("TRIM((pi.selling_price * py.total_item) - (pi.capital_price * py.total_item))+0 as profit"),
                DB::raw("TRIM(pi.selling_price * py.total_item)+0 as overall_price"),
                'branches.branch_name',
                'users.fullname as created_by',
            );

        $item = $item->where('py.isDeleted', '=', 0);

        if ($this->branch_id) {
            $item = $item->where('mp.branch_id', '=', $this->branch_id);
        }

        if ($this->date) {

          $item = $item->where(DB::raw("MONTH(py.created_at)"), $this->month)
          ->where(DB::raw("YEAR(py.created_at)"), $this->year);
        }

        if ($this->orderby) {
            $item = $item->orderBy($this->column, $this->orderby);
        }

        $item = $item->orderBy('py.id', 'desc');

        $item = $item->get();

        $val = 1;
        foreach ($item as $key) {
            $key->number = $val;
            $val++;
        }

        return $item;
    }

    public function headings(): array
    {
        return [
            ['No.', 'Tanggal Dibuat', 'No. Pembayaran', 'Nama Barang', 'Kategori Barang', 'Jumlah',
                'Harga Modal', 'Harga Jual', 'Keuntungan', 'Harga Keseluruhan', 'Cabang', 'Dibuat Oleh',
            ],
        ];
    }

    public function title(): string
    {
        return 'Data Laporan Keuangan';
    }

    public function map($item): array
    {
        $res = [
            [
                $item->number,
                $item->created_at,
                $item->payment_number,
                $item->item_name,
                $item->category,
                $item->total_item,
                number_format($item->capital_price, 2, ".", ","),
                number_format($item->selling_price, 2, ".", ","),
                number_format($item->profit, 2, ".", ","),
                number_format($item->overall_price, 2, ".", ","),
                $item->branch_name,
                $item->created_by,
            ],
        ];
        return $res;
    }
}
