<?php

use App\Http\Controllers\BusinessController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

include_once 'install_r.php';

Route::get('/', function () {
    return view('welcome');
});

Route::get('/check-domain', function (\Illuminate\Http\Request $request) {
    $trusted = array_filter(array_map(
        'trim',
        explode(',', env('CADDY_TRUSTED_IPS', '127.0.0.1,::1'))
    ));

    if (!in_array($request->server('REMOTE_ADDR'), $trusted)) {
        abort(403);
    }

    $domain = $request->query('domain');
    if (!$domain) {
        return response('', 400);
    }

    $exists = \Stancl\Tenancy\Database\Models\Domain::where('domain', $domain)->exists();
    return response('', $exists ? 200 : 404);
});

Auth::routes();

Route::get('/business/register', [BusinessController::class, 'getRegister'])->name('business.getRegister');
Route::post('/business/register', [BusinessController::class, 'postRegister'])->name('business.postRegister');
Route::post('/business/register/check-username', [BusinessController::class, 'postCheckUsername'])->name('business.postCheckUsername');
Route::post('/business/register/check-email', [BusinessController::class, 'postCheckEmail'])->name('business.postCheckEmail');
