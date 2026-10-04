<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\EaseOfDoingBusiness;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestEaseOfDoingBusinessController extends Controller
{
    public function index(Request $request): Response
    {
        $easeOfDoingBusinesses = EaseOfDoingBusiness::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->paginate(9)
            ->withQueryString();

        return Inertia::render(
            'HubunganInvestor/EaseOfDoingBusiness',
            [
                'easeOfDoingBusinesses' => $easeOfDoingBusinesses,
            ]
        );
    }
}
