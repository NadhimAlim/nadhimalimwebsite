<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceAccountController extends Controller
{
    public function login()
    {
        if (Auth::check()) return redirect()->intended(route('marketplace.checkout.cart'));
        return view('marketplace.account', ['mode' => 'login']);
    }

    public function authenticate(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (!Auth::attempt($data, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi tidak cocok.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return redirect()->intended(route('marketplace.checkout.cart'));
    }

    public function register()
    {
        if (Auth::check()) return redirect()->intended(route('marketplace.checkout.cart'));
        return view('marketplace.account', ['mode' => 'register']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->intended(route('marketplace.checkout.cart'))->with('success', 'Akun berhasil dibuat. Lanjutkan checkout Anda.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('marketplace.index')->with('cart_success', 'Anda berhasil keluar dari akun.');
    }

    public function orders(Request $request)
    {
        if (!Auth::check()) return redirect()->guest(route('marketplace.account.login'));
        $orders = MarketplaceOrder::with('items')->where('user_id', Auth::id())->latest()->paginate(10);
        return view('marketplace.orders', compact('orders'));
    }
}
