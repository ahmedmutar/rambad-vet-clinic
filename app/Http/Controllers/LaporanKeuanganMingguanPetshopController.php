<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Exports\LaporanKeuanganMingguanPetShop;

class LaporanKeuanganMingguanPetshopController extends Controller
{
    public function index(Request $request)
    {
        $item = DB::table('petshops as py')
            ->join('master_petshops as mp', 'py.master_petshop_id', '=', 'mp.id')
            ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
            ->join('price_items as pi', 'loi.id', '=', 'pi.list_of_items_id')
            ->join('category_item as ci', 'loi.category_item_id', '=', 'ci.id')
            ->join('users', 'py.user_id', '=', 'users.id')
            ->join('branches', 'mp.branch_id', '=', 'branches.id')
            ->select(
                'py.id',
                'mp.payment_number',
                DB::raw("DATE_FORMAT(py.created_at, '%d/%m/%Y') as created_at"),
                'loi.item_name',
                'ci.category_name as category',
                'py.total_item',
                DB::raw("TRIM(pi.capital_price * py.total_item)+0 as capital_price"),
                DB::raw("TRIM(pi.selling_price * py.total_item)+0 as selling_price"),
                DB::raw("TRIM((pi.selling_price * py.total_item) - (pi.capital_price * py.total_item))+0 as profit"),
                DB::raw("TRIM(pi.selling_price * py.total_item)+0 as overall_price"),
                'branches.id as branch_id',
                'branches.branch_name',
                'users.id as user_id',
                'users.fullname as created_by',
            );

        $item = $item->where('py.isDeleted', '=', 0);

        if ($request->branch_id && $request->user()->role == 'admin') {
            $item = $item->where('mp.branch_id', '=', $request->branch_id);
        }

        if ($request->user()->role == 'kasir') {
            $item = $item->whereBetween('mp.branch_id', '=', $request->user()->branch_id);
        }

        if ($request->date_from && $request->date_to) {

            $item = $item->whereBetween(DB::raw('DATE(py.created_at)'), [$request->date_from, $request->date_to]);
        }

        if ($request->orderby) {
            $item = $item->orderBy($request->column, $request->orderby);
        }

        $item = $item->orderBy('py.id', 'desc');

        $item = $item->get();

        //=======================================================
        $avg = DB::table('petshops as py')
            ->join('master_petshops as mp', 'py.master_petshop_id', '=', 'mp.id')
            ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
            ->join('price_items as pi', 'loi.id', '=', 'pi.list_of_items_id')
            ->join('users', 'py.user_id', '=', 'users.id')
            ->join('branches', 'users.branch_id', '=', 'branches.id')
            ->select(
                DB::raw("TRIM(SUM(pi.capital_price * py.total_item))+0 as capital_price"),
                DB::raw("TRIM(SUM(pi.selling_price * py.total_item))+0 as selling_price"),
                DB::raw("TRIM(SUM((pi.selling_price * py.total_item) - (pi.capital_price * py.total_item)))+0 as profit")
            );

        $avg = $avg->where('py.isDeleted', '=', 0);

        if ($request->branch_id && $request->user()->role == 'admin') {
            $avg = $avg->where('mp.branch_id', '=', $request->branch_id);
        }

        if ($request->user()->role == 'kasir') {
            $avg = $avg->where('mp.branch_id', '=', $request->user()->branch_id);
        }

        if ($request->date_from && $request->date_to) {

            $avg = $avg->whereBetween(DB::raw('DATE(py.created_at)'), [$request->date_from, $request->date_to]);
        }

        $avg = $avg->first();

        return response()->json([
            'data' => $item,
            'capital_price' => $avg->capital_price,
            'selling_price' => $avg->selling_price,
            'profit' => $avg->profit,
        ], 200);
    }

    public function download_report(Request $request)
    {
        if ($request->user()->role == 'kasir') {
            return response()->json([
                'message' => 'The user role was invalid.',
                'errors' => ['Akses User tidak diizinkan!'],
            ], 403);
        }

        return (new LaporanKeuanganMingguanPetShop($request->orderby, $request->column, $request->date_from, $request->date_to, $request->branch_id))
            ->download('Laporan Keuangan Mingguan.xlsx');
    }
}
