<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ListofItems;
use App\Models\Master_Petshop;
use App\Exports\RecapPayment;
use App\Models\Petshop;
use DB;
use Illuminate\Http\Request;
use Validator;
use PDF;

class PetshopController extends Controller
{
    public function index(Request $request)
    {
        if ($request->keyword) {

            $res = $this->Search($request);

            $payment = DB::table('petshops as ps')
                ->join('master_petshops as mp', 'py.master_petshop_id', '=', 'mp.id')
                ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
                ->join('price_items as pi', 'loi.id', '=', 'pi.list_of_items_id')
                ->join('category_item as ci', 'loi.category_item_id', '=', 'ci.id')
                ->join('users', 'py.user_id', '=', 'users.id')
                ->join('branches', 'mp.branch_id', '=', 'branches.id');

            $payment = $payment->select(
                'ps.id',
                'mp.payment_number',
                'loi.item_name',
                'ps.total_item',
                'ci.category_name as category',
                DB::raw("TRIM(pi.selling_price)+0 as each_price"),
                DB::raw("TRIM(pi.selling_price * py.total_item)+0 as overall_price"),
                'branches.id as branch_id',
                'branches.branch_name',
                'users.id as user_id',
                'users.fullname as created_by',
                DB::raw("DATE_FORMAT(ps.created_at, '%d/%m/%Y') as created_at"))
                ->where('py.isDeleted', '=', 0);

            if ($res) {
                $payment = $payment->where($res, 'like', '%' . $request->keyword . '%');
            } else {
                $data = [];
                return response()->json($data, 200);
            }

            if ($request->branch_id && $request->user()->role == 'admin') {
                $payment = $payment->where('loi.branch_id', '=', $request->branch_id);
            }

            if ($request->user()->role == 'kasir') {
                $payment = $payment->where('mp.branch_id', '=', $request->user()->branch_id);
            }

            if ($request->orderby) {
                $payment = $payment->orderBy($request->column, $request->orderby);
            }

            $payment = $payment->orderBy('py.id', 'desc');

            $payment = $payment->get();

        } else {

            $payment = DB::table('petshops as py')
                ->join('master_petshops as mp', 'py.master_petshop_id', '=', 'mp.id')
                ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
                ->join('price_items as pi', 'loi.id', '=', 'pi.list_of_items_id')
                ->join('category_item as ci', 'loi.category_item_id', '=', 'ci.id')
                ->join('users', 'py.user_id', '=', 'users.id')
                ->join('branches', 'mp.branch_id', '=', 'branches.id');

            $payment = $payment->select(
                'py.id',
                'mp.payment_number',
                'loi.item_name',
                'py.total_item',
                'ci.category_name as category',
                DB::raw("TRIM(pi.selling_price)+0 as each_price"),
                DB::raw("TRIM(pi.selling_price * py.total_item)+0 as overall_price"),
                'branches.id as branch_id',
                'branches.branch_name',
                'users.id as user_id',
                'users.fullname as created_by',
                DB::raw("DATE_FORMAT(py.created_at, '%d/%m/%Y') as created_at"))
                ->where('py.isDeleted', '=', 0);

            if ($request->branch_id && $request->user()->role == 'admin') {
                $payment = $payment->where('loi.branch_id', '=', $request->branch_id);
            }

            if ($request->user()->role == 'kasir') {
                $payment = $payment->where('mp.branch_id', '=', $request->user()->branch_id);
            }

            if ($request->orderby) {
                $payment = $payment->orderBy($request->column, $request->orderby);
            }

            $payment = $payment->orderBy('py.id', 'desc');

            $payment = $payment->get();

            return response()->json($payment, 200);
        }
    }

    private function Search($request)
    {
        $temp_column = '';

        $payment = DB::table('payments as py')
            ->join('master_payments as mp', 'py.master_payment_id', '=', 'mp.id')
            ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
            ->join('users', 'loi.user_id', '=', 'users.id')
            ->join('branches', 'loi.branch_id', '=', 'branches.id')
            ->select(
                'mp.payment_number',
                'loi.item_name',
                'loi.category',
                'branches.branch_name',
                'users.fullname as created_by')
            ->where('py.isDeleted', '=', 0);

        if ($request->keyword) {
            $payment = $payment->where('mp.payment_number', 'like', '%' . $request->keyword . '%');
        }

        if ($request->user()->role == 'kasir') {
            $payment = $payment->where('loi.branch_id', '=', $request->user()->branch_id);
        }

        $payment = $payment->get();

        if (count($payment)) {
            $temp_column = 'mp.payment_number';
            return $temp_column;
        }
        //===================================

        $payment = DB::table('payments as py')
            ->join('master_payments as mp', 'py.master_payment_id', '=', 'mp.id')
            ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
            ->join('users', 'loi.user_id', '=', 'users.id')
            ->join('branches', 'loi.branch_id', '=', 'branches.id')
            ->select(
                'mp.payment_number',
                'loi.item_name',
                'loi.category',
                'branches.branch_name',
                'users.fullname as created_by')
            ->where('py.isDeleted', '=', 0);

        if ($request->keyword) {
            $payment = $payment->where('loi.item_name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->user()->role == 'kasir') {
            $payment = $payment->where('loi.branch_id', '=', $request->user()->branch_id);
        }

        $payment = $payment->get();

        if (count($payment)) {
            $temp_column = 'loi.item_name';
            return $temp_column;
        }
        //===================================

        $payment = DB::table('payments as py')
            ->join('master_payments as mp', 'py.master_payment_id', '=', 'mp.id')
            ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
            ->join('users', 'loi.user_id', '=', 'users.id')
            ->join('branches', 'loi.branch_id', '=', 'branches.id')
            ->select(
                'mp.payment_number',
                'loi.item_name',
                'loi.category',
                'branches.branch_name',
                'users.fullname as created_by')
            ->where('py.isDeleted', '=', 0);

        if ($request->keyword) {
            $payment = $payment->where('loi.category', 'like', '%' . $request->keyword . '%');
        }

        if ($request->user()->role == 'kasir') {
            $payment = $payment->where('loi.branch_id', '=', $request->user()->branch_id);
        }

        $payment = $payment->get();

        if (count($payment)) {
            $temp_column = 'loi.category';
            return $temp_column;
        }
        //===================================

        $payment = DB::table('payments as py')
            ->join('master_payments as mp', 'py.master_payment_id', '=', 'mp.id')
            ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
            ->join('users', 'loi.user_id', '=', 'users.id')
            ->join('branches', 'loi.branch_id', '=', 'branches.id')
            ->select(
                'mp.payment_number',
                'loi.item_name',
                'loi.category',
                'branches.branch_name',
                'users.fullname as created_by')
            ->where('py.isDeleted', '=', 0);

        if ($request->keyword) {
            $payment = $payment->where('branches.branch_name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->user()->role == 'kasir') {
            $payment = $payment->where('loi.branch_id', '=', $request->user()->branch_id);
        }

        $payment = $payment->get();

        if (count($payment)) {
            $temp_column = 'branches.branch_name';
            return $temp_column;
        }
        //===================================

        $payment = DB::table('payments as py')
            ->join('master_payments as mp', 'py.master_payment_id', '=', 'mp.id')
            ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
            ->join('users', 'loi.user_id', '=', 'users.id')
            ->join('branches', 'loi.branch_id', '=', 'branches.id')
            ->select(
                'mp.payment_number',
                'loi.item_name',
                'loi.category',
                'branches.branch_name',
                'users.fullname as created_by')
            ->where('py.isDeleted', '=', 0);

        if ($request->keyword) {
            $payment = $payment->where('users.fullname', 'like', '%' . $request->keyword . '%');
        }

        if ($request->user()->role == 'kasir') {
            $payment = $payment->where('loi.branch_id', '=', $request->user()->branch_id);
        }

        $payment = $payment->get();

        if (count($payment)) {
            $temp_column = 'users.fullname';
            return $temp_column;
        }
        //===================================
    }

    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'list_of_items.*.list_of_item_id' => 'required|numeric',
            'list_of_items.*.total_item' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return response()->json([
                'message' => 'Data yang dimasukkan tidak valid!',
                'errors' => $errors,
            ], 422);
        }

        $items = $request->list_of_items;
        $result_items = json_decode($items, true);
        //$items;
        //json_decode($items, true);

        $lastnumber = DB::table('master_petshops')
            ->where('branch_id', '=', $request->branch_id)
            ->count();

        $branch = Branch::find($request->branch_id);

        $payment_number = 'RVS-P-' . $branch->branch_code . '-' . str_pad($lastnumber + 1, 4, 0, STR_PAD_LEFT);

        $master_petshop = Master_Petshop::create([
            'payment_number' => $payment_number,
            'user_id' => $request->user()->id,
            'branch_id' => $request->branch_id,
        ]);

        foreach ($result_items as $value) {

            $find_item = ListofItems::find($value['list_of_item_id']);

            if (is_null($find_item)) {
                return response()->json([
                    'message' => 'The data was invalid.',
                    'errors' => ['Barang tidak ada!'],
                ], 422);
            }

            $res_value = $find_item->total_item - $value['total_item'];

            if ($res_value < 0) {
                return response()->json([
                    'message' => 'The data was invalid.',
                    'errors' => ['Stok Barang ' . $find_item->item_name . ' kurang atau habis!'],
                ], 422);
            }

            $find_item->total_item = $res_value;
            $find_item->user_update_id = $request->user()->id;
            $find_item->updated_at = \Carbon\Carbon::now();
            $find_item->save();

            $payment = Petshop::create([
                'list_of_item_id' => $value['list_of_item_id'],
                'total_item' => $value['total_item'],
                'master_petshop_id' => $master_petshop->id,
                'user_id' => $request->user()->id,
            ]);

        }

        return response()->json(
            [
                'message' => 'Tambah Data Berhasil!',
                'master_payment_id' => $master_petshop->id,
            ], 200
        );

    }

    public function filteritem(Request $request)
    {
        $item = DB::table('price_items as pi')
            ->join('list_of_items as loi', 'pi.list_of_items_id', '=', 'loi.id')
            ->join('category_item as ci', 'loi.category_item_id', '=', 'ci.id')
            ->select('pi.id as id', 'loi.item_name as item_name', 'ci.category_name as category', DB::raw("TRIM(pi.selling_price)+0 as selling_price"))
            ->where('pi.isDeleted', '=', 0)
            ->where('loi.branch_id', '=', $request->branch_id)
            ->get();

        return response()->json($item, 200);
    }

    public function print_receipt(Request $request)
    {
        $data_header = DB::table('master_petshops as mp')
            ->join('users', 'mp.user_id', '=', 'users.id')
            ->join('branches', 'mp.branch_id', '=', 'branches.id')
            ->select(
                'branches.branch_name',
                'branches.address',
                'mp.payment_number',
                'users.fullname as cashier_name',
                DB::raw("DATE_FORMAT(mp.created_at, '%d %b %Y %H:%i:%s') as paid_time"))
            ->where('mp.id', '=', $request->master_payment_id)
            ->get();

        $data_detail = DB::table('petshops as py')
            ->join('master_petshops as mp', 'py.master_petshop_id', '=', 'mp.id')
            ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
            ->join('price_items as pi', 'loi.id', '=', 'pi.list_of_items_id')

            ->select(
                'loi.item_name',
                'py.total_item',
                DB::raw("TRIM(pi.selling_price)+0 as each_price"),
                DB::raw("TRIM(py.total_item * pi.selling_price)+0 as total_price"))
            ->where('py.master_petshop_id', '=', $request->master_payment_id)
            ->get();

        $price_overall = DB::table('petshops as py')
            ->join('master_petshops as mp', 'py.master_petshop_id', '=', 'mp.id')
            ->join('list_of_items as loi', 'py.list_of_item_id', '=', 'loi.id')
            ->join('price_items as pi', 'loi.id', '=', 'pi.list_of_items_id')
            ->select(
                DB::raw("TRIM(SUM(py.total_item * pi.selling_price))+0 as price_overall"))
            ->where('py.master_petshop_id', '=', $request->master_payment_id)
            ->first();

        $data = [
            'data_header' => $data_header,
            'data_detail' => $data_detail,
            'price_overall' => $price_overall,
        ];

        $find_payment_number = DB::table('master_petshops')
            ->select('payment_number')
            ->where('id', '=', $request->master_payment_id)
            ->first();

        $pdf = PDF::loadview('petshop-pdf', $data);

        return $pdf->download($find_payment_number->payment_number . '.pdf');
    }

    public function download_report_excel(Request $request)
    {
        $date = "";
        $date = \Carbon\Carbon::now()->format('d-m-y');

        $filename = 'Rekap Pembayaran ' . $date . '.xlsx';

        $branch_id = "";

        if ($request->user()->role == 'kasir') {
            $branch_id = $request->user()->branch_id;
        } else {
            $branch_id = $request->branch_id;
        }

        return (new RecapPayment($request->orderby, $request->column, $request->keyword, $branch_id))
            ->download($filename);
    }


}
