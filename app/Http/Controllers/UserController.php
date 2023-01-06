<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $user = auth()->user();

        $recentOrders = $user->orders()
            ->latest()
            ->take(3)
            ->with(['products', 'products.images'])
            ->get();

        return view('users.show', compact('user', 'recentOrders'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($userId)
    {
        if (auth()->user()->id != $userId) {
            return abort(403);
        }

        $user = User::where('id', $userId)->first();

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.auth()->id()],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string'],
            'country' => 'required'
        ]);

        $user = auth()->user();
        $user->updateProfile($request);

        return back()->with(
            'flash',
            'Your account has been successfully updated!'
        );
    }
}
