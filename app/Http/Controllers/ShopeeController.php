<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ShopeeController extends Controller
{
    private $partnerId;
    private $partnerKey;
    private $shopId;
    private $apiBaseUrl;

    public function __construct()
    {
        $this->partnerId = env('SHOPEE_PARTNER_ID');
        $this->partnerKey = env('SHOPEE_PARTNER_KEY');
        $this->shopId = env('SHOPEE_SHOP_ID');
        $this->apiBaseUrl = 'https://partner.test-stable.shopeemobile.com/api/v2/';
    }

    public function getShopInfo()
    {
        $partnerId = env('SHOPEE_PARTNER_ID');
        $partnerKey = env('SHOPEE_PARTNER_KEY');
        $timestamp = time();
        $path = '/shop/get_shop_info';

        // Buat Signature
        $baseString = sprintf('%s%s%s', $partnerId, $path, $timestamp);
        $sign = hash_hmac('sha256', $baseString, $partnerKey);

        $response = Http::get("https://partner.shopeemobile.com/api/v2{$path}", [
            'partner_id' => $partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign
        ]);

        return $response->json();
    }

    public function postProduct(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'required|string|max:3000',
            'price' => 'required|numeric|min:1',
            'stock' => 'required|integer|min:1',
            'category_id' => 'required|integer',
        ]);

        $timestamp = time();
        $path = 'product/add';

        // Buat signature
        $baseString = $this->partnerId . $path . $timestamp . $this->partnerKey;
        $signature = hash_hmac('sha256', $baseString, $this->partnerKey);

        // Data produk
        $data = [
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price'],
            'stock' => $validatedData['stock'],
            'category_id' => $validatedData['category_id'],
            'shopid' => $this->shopId,
        ];

        // Kirim request ke Shopee Open API
        $client = new Client();
        $response = $client->post($this->apiBaseUrl . $path, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $signature,
                'Partner-Id' => $this->partnerId,
                'Timestamp' => $timestamp,
            ],
            'json' => $data,
        ]);

        $result = json_decode($response->getBody(), true);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json(['message' => 'Product successfully added to Shopee', 'data' => $result]);
    }
}
