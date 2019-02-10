<?php

namespace App\Http\Controllers\Patient;
use App\Patient;
//use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Controller;
use App\Mail\AdminResetPassword;
use Carbon\Carbon;
use DB;
use Mail;

class PatientAuth extends Controller {
    //

    public function login() {
        return view('patients.login');
    }

    public function dologin() {
        $rememberme = request('rememberme') == 1?true:false;
        if (patient()->attempt(['email' => request('email'), 'password' => request('password')], $rememberme)) {
            return view('patients.home');
        } else {
            session()->flash('error', trans('patient.inccorrect_information_login'));
            return redirect(url('patient.login'));
        }
    }

    public function logout() {
        auth()->guard('patient')->logout();
        return redirect(url('patient/login'));
    }

    public function forgot_password() {
        return view('patient.forgot_password');
    }

    public function forgot_password_post() {
        $patient = Patient::where('email', request('email'))->first();
        if (!empty($patient)) {
            $token = app('auth.password.broker')->createToken($patient);
            $data  = DB::table('password_resets')->insert([
                'email'      => $patient->email,
                'token'      => $token,
                'created_at' => Carbon::now(),
            ]);
            Mail::to($patient->email)->send(new patientResetPassword(['data' => $patient, 'token' => $token]));
            session()->flash('success', trans('admin.the_link_reset_sent'));
            return back();
        }
        return back();
    }

    public function reset_password_final($token) {

        $this->validate(request(), [
            'password'              => 'required|confirmed',
            'password_confirmation' => 'required',
        ], [], [
            'password'              => 'Password',
            'password_confirmation' => 'Confirmation Password',
        ]);

        $check_token = DB::table('password_resets')->where('token', $token)->where('created_at', '>', Carbon::now()->subHours(2))->first();
        if (!empty($check_token)) {
            $patient = Patient::where('email', $check_token->email)->update([
                'email'    => $check_token->email,
                'password' => bcrypt(request('password'))
            ]);
            DB::table('password_resets')->where('email', request('email'))->delete();
            patient()->attempt(['email' => $check_token->email, 'password' => request('password')], true);
            return redirect(aurl());
        } else {
            return redirect(url('patient/forgot/password'));
        }
    }

    public function reset_password($token) {
        $check_token = DB::table('password_resets')->where('token', $token)->where('created_at', '>', Carbon::now()->subHours(2))->first();
        if (!empty($check_token)) {
            return view('patients.reset_password', ['data' => $check_token]);
        } else {
            return redirect(url('forgot/password'));
        }
    }
}
