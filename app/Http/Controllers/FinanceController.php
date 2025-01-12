<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MExpenseCategoryModel;
use App\Models\DExpenseModel;
use DataTables;

class FinanceController extends Controller
{
    public function expense(){
        return view('expense-list');
    }

    public function expenseDatatables(){
        $data = DExpenseModel::select('m_expense_category.name as category', 'd_expense.*')
            ->join('m_expense_category', 'm_expense_category.id', '=', 'd_expense.expense_category_id')
            ->orderBy('id','DESC')->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                return '<div class="edit-delete-action">
                    <a class="me-2 p-2 btn btn-success btn-sm edit-expense" href="javascript:void(0);" data-bs-toggle="modal"
                        data-bs-target="#add-expense" data-id="'.$row->id.'">
                        <i class="fas fa-pencil"></i>
                    </a>
                    <a class="btn btn-danger btn-sm p-2 del-expense" href="javascript:void(0);" data-id="'.$row->id.'">
                        <i class="fas fa-trash-can"></i>
                    </a>
                </div>';
            })
            ->editColumn('date', function($row){
                return date("d/m/Y", strtotime($row->date));
            })
            ->rawColumns(['action', 'date'])
            ->make(true);
    }

    public function editExpense($id)
    {
        $data = DExpenseModel::select('m_expense_category.name as category', 'd_expense.*')
            ->join('m_expense_category', 'm_expense_category.id', '=', 'd_expense.expense_category_id')
            ->find($id);

        $dataCat = MExpenseCategoryModel::where('status', '=', 1)->get();
        $category = '<label class="form-label">Expense Category</label>
                <select class="form-control" id="category" name="category">
                <option>Choose Expense Category</option>';
        foreach($dataCat as $dt){
            $category .= '<option value="'.$dt->id.'">'.$dt->name.'</option>';
        }
        $category .= '</select>';

        if($data){
            $return = array(
                "category_list" => $category,
                "category_id" => $data->expense_category_id,
                "name" => $data->name,
                "date" => date('d/m/Y', strtotime($data->date)),
                "amount" => $data->amount,
                "reference" => $data->reference,
                "desc" => $data->description,
                "status" => true
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Data not found"
            );
        }

        echo json_encode($return);
    }

    public function storeExpense(Request $request)
    {
        $id = $request->input('expense_id');
        $date_arr = explode("/", $request->tanggal);
        $date = date('Y-m-d', strtotime($date_arr[2]."-".$date_arr[1]."-".$date_arr[0]));


        if($id == ""){
            $data = new DExpenseModel;
        } else {
            $data = DExpenseModel::find($id);
        }

        $data->name = $request->input('nama');
        $data->expense_category_id = $request->input('category');
        $data->name = $request->input('nama');
        $data->date = $date;
        $data->amount = $request->input('amount');
        $data->reference = $request->input('reference');
        $data->description = $request->input('desc');
        $data->save();

        if($data){
            $return = array(
                "status" => true,
                "msg" => "Successfully saved"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }

    public function deleteExpense($id)
    {
        $dataKategori = DExpenseModel::find($id);
        $dataKategori->delete();

        if($dataKategori){
            $return = array(
                "status" => true,
                "msg" => "Successfully deleted"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }
}
