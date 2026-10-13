<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListProductLogsRequest;
use App\Http\Resources\ProductLogResource;
use App\Services\ProductLogService;

class ProductLogController extends Controller
{
    public function __construct(private readonly ProductLogService $logService)
    {
    }

    public function index(ListProductLogsRequest $request)
    {
        $logs = $this->logService->paginate($request->validated(), $request->integer('per_page', 15));

        return ProductLogResource::collection($logs);
    }
}
