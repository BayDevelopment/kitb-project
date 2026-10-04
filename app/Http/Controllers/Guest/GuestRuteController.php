<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Rute;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestRuteController extends Controller
{
    public function index(): Response
    {
        $rutes = Rute::query()->aktif()->ordered()->get();
        return Inertia::render('HubunganInvestor/RutePelayaranLokasi', ['rutes' => $rutes,]);
    }
}
