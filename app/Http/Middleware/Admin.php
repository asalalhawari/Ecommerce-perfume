<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // تحقق من وجود مستخدم مسجل دخول
        if (Auth::check()) {
            if ($request->user()->role == 'admin') {
                return $next($request);
            } else {
                request()->session()->flash('error', 'You do not have any permission to access this page');
                // إعادة التوجيه إلى الصفحة الرئيسية أو أي صفحة أخرى مناسبة
                return redirect()->route('home'); // تأكد من وجود route بالاسم 'home'
            }
        }

        // إذا لم يكن المستخدم مسجلاً دخول، إعادة التوجيه إلى صفحة تسجيل الدخول أو الصفحة الرئيسية
        return redirect()->route('login'); // تأكد من وجود route بالاسم 'login'
    }
}
