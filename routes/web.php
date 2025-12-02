<?php

use App\Http\Controllers\PayController;
use App\Http\Controllers\PaymentController;
use App\Http\Middleware\Founder;
use App\Http\Middleware\SuperAdmin;
use App\Livewire\PurchaseIcdl;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

//Volt::route('/', 'welcome')->name('home');

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Volt::route('select_role', 'auth.select-role')->name('select_role');
});

Route::middleware('guest')->group(function () {
    Volt::route('registration', 'auth.my-register')->name('registration');
    Volt::route('forgotten-password', 'auth.my-forgot-password')
        ->name('forgotten.password');
});



Volt::route('fields', 'field.crud')->name('fields')->middleware(['auth']);

Volt::route('standards', 'standard.index')->name('standards')->middleware(['auth']);
Volt::route('standard/create', 'standard.create')->name('create_standard')->middleware(['auth']);
Volt::route('standard/{standard}/edit', 'standard.edit')->name('edit_standard')->middleware(['auth', 'signed']);
Volt::route('standard/{standard}/chapters', 'standard.chapter.crud')->name('chapters')->middleware(['auth', 'signed']);

Volt::route('question/{sid}/{cid}/index', 'question.index')->name('questions')->middleware(['auth', 'signed']);
Volt::route('question/{sid}/{cid}/create', 'question.create')->name('create_question')->middleware(['auth', 'signed']);
Volt::route('question/{question}/edit', 'question.edit')->name('edit_question')->middleware(['auth', 'signed']);


Volt::route('sa/users/index', 'user.index.sa')->name('users.for.sa')
    ->middleware(['auth', SuperAdmin::class]);
Volt::route('founder/users/index', 'user.index.founder')->name('users.for.founder')
    ->middleware(['auth', Founder::class]);

Volt::route('user/{user}/show', 'user.show')->name('show_user')->middleware(['auth', 'signed']);


Volt::route('sa/institutes/index', 'institute.index.sa')->name('institutes.for.sa')
    ->middleware(['auth', SuperAdmin::class]);
Volt::route('founder/institutes/index', 'institute.index.founder')->name('institutes.for.founder')
    ->middleware(['auth', Founder::class]);


Volt::route('institute/{institute}/founders', 'institute.founder.index')->name('institute_founders')->middleware(['auth', 'signed']);

Volt::route('roles', 'role.index')->name('roles')->middleware('auth');
Volt::route('role/{role}/show', 'role.show')->name('show_role')->middleware(['auth', 'signed']);


Volt::route('exams', 'exam.index')->name('exams')->middleware(['auth']);
Volt::route('exam/create', 'exam.create')->name('exam_create')->middleware(['auth']);
Volt::route('exam/{exam}/start', 'exam.start')->name('exam_start')->middleware(['auth']);
Volt::route('exam_user/{examUser}/take', 'exam.take')->name('exam.take')->middleware(['auth']);
Volt::route('exam_user/{examUser}/result', 'exam.result')->name('exam.result')->middleware(['auth']);



use App\Http\Controllers\TestSepController;
//Volt::route('payment/callback', 'payment.callback');

Route::get('sep/test', [TestSepController::class, 'start'])->name('sep.test');
Route::post('sep/pay', [TestSepController::class, 'pay'])->name('sep.pay');
//Route::match(['get', 'post'], 'sep/callback', [TestSepController::class, 'callback'])->name('sep.callback');

Volt::route('sepp/pay', 'pay');


Route::get('/purchase-icdl', PurchaseIcdl::class)->name('icdl.purchase');

Route::post('/payment/callback', [PaymentController::class, 'callback'])
    ->withoutMiddleware(['web'])->name('payment.callback');


//Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');



Route::post('pay/callback', [PayController::class, 'call_back'])->withoutMiddleware(['web'])->name('pay.callback');



Volt::route('final_written_questions', 'final-question.written.index')->name('written_questions');
Volt::route('final_written_questions/{standard}', 'final-question.written.standard-questions')->name('written_standard_questions');
