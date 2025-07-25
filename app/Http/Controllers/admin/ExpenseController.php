<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\User;
use App\Models\Group;
use App\Models\Split;


class ExpenseController extends Controller
{
     
    function index()
    {
       $expenses = Expense::with(['user', 'group', 'splits.user'])->get();

        return view('admin.expenses.index', compact('expenses'));
    }

    function create()
    { 
        $users= User::all();
        $groups= Group::all();
        return view('admin.expenses.create', compact('users', 'groups'));
         
    }

    function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'nullable|exists:groups,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'status' => 'in:pending,approved',
            'notes' => 'nullable|string|max:500',
        ]);

       $expense = Expense::create($request->all());
        if ($request->group_id) {
    $group = Group::with('users')->find($request->group_id);
    $members = $group->users;
             $split_amount = round($request->amount / $members->count());
             foreach($members as $member){
               if($member->id == $request->user_id){
                    $member->lent_total +=$request->amount-$split_amount;
                    Split::create([
                        'user_id'=>$member->id,
                        'expense_id' => $expense->id,
                        'amount' => $request->amount - $split_amount,   
                        'type' => 'lent',
                    ]);
               } 
               else{
                $member->owed_total += $split_amount;
                Split::create([
                    'user_id' => $member->id,
                    'expense_id' => $expense->id,
                    'amount' => $split_amount,
                    'type' => 'owned',
                ]);
               }
            }
        }

        return redirect()->route('admin.expenses.index')->with('success', 'Expense created successfully.');
    }

     function edit($id){
        $expense = Expense::findOrFail($id);
        $users = User::all();
        $groups = Group::all();
        return view('admin.expenses.edit', compact('expense', 'users', 'groups'));
     }

     function update(Request $request, $id)
     {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'nullable|exists:groups,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'status' => 'in:pending,approved',
            'notes' => 'nullable|string|max:500',
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update($request->all());

        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated successfully.');
     }  

   function destroy($id)
{
    $expense = Expense::with('splits.user')->findOrFail($id);

    // Optional: reverse the splits (reduce lent/owed totals if you're maintaining them)
    foreach ($expense->splits as $split) {
        $user = $split->user;
        if ($split->type == 'lent') {
            $user->lent_total -= $split->amount;
        } elseif ($split->type == 'owed') {
            $user->owed_total -= $split->amount;
        }
        $user->save();
    }

    $expense->splits()->delete(); // delete splits
    $expense->delete(); // delete the expense

    return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted successfully.');
}

}



 

 