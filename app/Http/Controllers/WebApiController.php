<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class WebApiController extends Controller
{
    /**
     * Memanggil API internal dan mengembalikan data array hasil decode JSON
     */
    protected function callApi($method, $uri, $data = [])
    {
        $method = strtoupper($method);
        $content = null;
        $parameters = [];

        $files = [];
        
        // Cek jika ada file (UploadedFile) di dalam $data, pisahkan ke $files
        foreach ($data as $key => $value) {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                $files[$key] = $value;
                unset($data[$key]);
            } elseif (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if ($subValue instanceof \Illuminate\Http\UploadedFile) {
                        $files[$key][$subKey] = $subValue;
                        unset($data[$key][$subKey]);
                    }
                }
            }
        }

        $request = \Illuminate\Http\Request::create($uri, $method, $data, [], $files, [
            'HTTP_ACCEPT' => 'application/json',
        ]);
        
        // Teruskan session & auth user yang login di web ke request API internal
        if (auth()->check()) {
            $request->setUserResolver(fn() => auth()->user());
        }

        $originalRequest = request();
        app()->instance('request', $request);

        try {
            $response = Route::dispatch($request);
        } finally {
            app()->instance('request', $originalRequest);
        }
        
        $decoded = json_decode($response->getContent(), true);

        if ($response->getStatusCode() !== 200) {
            \Illuminate\Support\Facades\Log::error('API Error in WebApiController', [
                'uri' => $uri,
                'status' => $response->getStatusCode(),
                'response' => $decoded,
            ]);
        }

        return $decoded;
    }
}
