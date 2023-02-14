<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $addresses = DB::table('addresses')->paginate(10);

        return view('address.index', [
            'addresses' => $addresses
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('address.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'street' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required|numeric',
            'phone' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->route('address.create')
                ->withErrors($validator)
                ->withInput();
        }
        $validated = $validator->validated();

        DB::table('addresses')->insert([
            'name'      => $validated['name'],
            'street'    => $validated['street'],
            'city'      => $validated['city'],
            'state'     => $validated['state'],
            'zip'       => $validated['zip'],
            'phone'     => $validated['phone'],
        ]);

        return redirect()->route('address.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        DB::table('addresses')->delete($id);
        return redirect()->route('address.index');
    }
}
