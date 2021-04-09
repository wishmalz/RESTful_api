<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BuyerSellerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Buyer $buyer)
    {
        $sellers = $buyer->transactions()
            ->with('product.sellers')
            ->get()
            ->pluck('product.sellers')
            ->unique('id')
            ->values();

        return $this->returnAll($sellers);
    }
}
