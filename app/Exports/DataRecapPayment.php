<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DataRecapPayment implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle, WithMapping
{
    protected $orderby;
    protected $column;
    protected $date;
    protected $branch_id;

    public function __construct($orderby, $column, $keyword, $branch_id)
    {
        $this->orderby = $orderby;
        $this->column = $column;
        $this->keyword = $keyword;
        $this->branch_id = $branch_id;
    }
    public function collection()
    {
        $payment = DB::table('petshops as py')
            ->join('master_petshops as mp', 'py.master_petshop_id', '=', 'mp.id')
            ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
            ->join('price_items as pi', 'loi.id', '=', 'pi.list_of_items_id')
            ->join('category_item as ci', 'loi.category_item_id', '=', 'ci.id')
            ->join('users', 'py.user_id', '=', 'users.id')
            ->join('branches', 'mp.branch_id', '=', 'branches.id');

        $payment = $payment->select(
            'py.id',
            'loi.item_name',
            'py.total_item',
            'ci.category_name as category',
            DB::raw("TRIM(pi.selling_price)+0 as each_price"),
            DB::raw("TRIM(pi.selling_price * py.total_item)+0 as overall_price"),
            'branches.id as branch_id',
            'branches.branch_name',
            'users.id as user_id',
            'users.fullname as created_by',
            DB::raw("DATE_FORMAT(py.created_at, '%d/%m/%Y') as created_at"));

        $payment = $payment->where('py.isDeleted', '=', 0);

        if ($this->branch_id) {
            $payment = $payment->where('loi.branch_id', '=', $this->branch_id);
        }

        if ($this->keyword) {

            $payment = $payment->where('loi.item_name', 'like', '%' . $this->keyword . '%')
                ->orwhere('branches.branch_name', 'like', '%' . $this->keyword . '%')
                ->orwhere('users.fullname', 'like', '%' . $this->keyword . '%');
        }

        if ($this->orderby) {
            $payment = $payment->orderBy($this->column, $this->orderby);
        }

        $payment = $payment->orderBy('py.id', 'desc');

        $payment = $payment->get();

        $val = 1;
        foreach ($payment as $key) {
            $key->number = $val;
            $val++;
        }

        return $payment;
    }

    public function headings(): array
    {
        return [
            ['No.', 'Tanggal Dibuat', 'Nama Barang', 'Kategori Barang', 'Jumlah', 'Harga Satuan', 'Harga Keseluruhan', 'Dibuat Oleh',
            ],
        ];
    }

    public function title(): string
    {
        return 'Data Pembayaran';
    }

    public function map($item): array
    {
        $res = [
            [
                $item->number,
                $item->created_at,
                $item->item_name,
                $item->category,
                $item->total_item,
                number_format($item->each_price, 2, ".", ","),
                number_format($item->overall_price, 2, ".", ","),
                $item->created_by,
            ],
        ];
        return $res;
    }
}
