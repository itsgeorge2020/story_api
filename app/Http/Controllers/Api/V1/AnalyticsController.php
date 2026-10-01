<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\AnalyticsPeriodRequest;
use App\Models\Story;
use App\Models\TelegramUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends BaseController
{
    public function users(AnalyticsPeriodRequest $request): JsonResponse
    {
        return $this->sendResponse($this->build(TelegramUser::query(), $request));
    }

    public function stories(AnalyticsPeriodRequest $request): JsonResponse
    {
        return $this->sendResponse($this->build(Story::query(), $request));
    }

    private function build(Builder $query, AnalyticsPeriodRequest $request): array
    {
        $from = $request->validated('from');
        $to = $request->validated('to');

        $periodQuery = (clone $query)->whereBetween('created_at', [
            $from . ' 00:00:00',
            $to . ' 23:59:59',
        ]);

        $byDay = (clone $periodQuery)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        return [
            'total' => (clone $query)->count(),
            'period_total' => $periodQuery->count(),
            'by_day' => $byDay,
        ];
    }
}
