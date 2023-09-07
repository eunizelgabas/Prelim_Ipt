<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function loginForm(){
        return view('auth.login');
    }

    public function registerForm(){
        return view('auth.register');
    }

    // public function login(Request $request) {
    //     $request->validate([
    //         'email' => 'required|string',
    //         'password' => 'required|string'
    //     ]);

    //     $user = $user->email;

    //     if($user->email_verified_at){
    //         return redirect('/dashboard');
    //     }else{
    //         return redirect('/')->with('error', 'Your account is not yet verified');
    //     }
    
    // }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);
    
        // Assuming you're using Laravel's built-in authentication system
        // to authenticate the user by email and password.
        // You should attempt to authenticate the user using the provided credentials.
        if (Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
            // Check if the authenticated user's email is verified
            if (auth()->user()->email_verified_at) {
                return redirect('/dashboard');
            } else {
                return redirect('/')->with('error', 'Your account is not yet verified');
            }
        } else {
            // Authentication failed
            return redirect('/')->with('error', 'Invalid credentials');
        }
    }

    public function register(Request $request){
        $request->validate([
            'name'      => 'required|string',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|confirmed|string|min:6'
        ]);

        $token = Str::random(24);

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => bcrypt($request->password),
            'remember_token'    => $token
        ]);

        // Mail::send('auth.verification-mail', ['user'=>$user], function($mail) use($user){
        //     $mail->to($user->email);
        //     $mail->subject('Account Verification');
        // });

        Mail::send('auth.verification-mail', ['user'=>$user], function($mail) use($user){
            $mail->to($user->email);
            $mail->subject('Account Verification');
        });

        return redirect('/')->with('message', ' Your account has been created. Please check your email for the verification link.');
    }

    public function verification(User $user, $token){
        if($user->remember_token !== $token){
            return redirect('/')->with('error', 'Invalid Token');
        }

        $user->email_verified_at = now();
        $user->save();

        return redirect('/')->with('message', 'Your account has been verified');

    }

    public function logout() {
        auth()->logout();
        return redirect('/');
    }

    public function dashboard(){
        return view('dashboard');
    }
}
