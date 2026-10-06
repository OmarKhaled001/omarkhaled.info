<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Seo\LlmsTxt;
use Illuminate\Http\Response;

class LlmsController extends Controller
{
    public function summary(LlmsTxt $llms): Response
    {
        return response($llms->summary(), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function full(LlmsTxt $llms): Response
    {
        return response($llms->full(), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
