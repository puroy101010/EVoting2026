<?php

namespace App\Logging;

use Monolog\Processor\ProcessorInterface;
use Illuminate\Support\Facades\Auth;

class InfinitekLoggerTap
{
    public function __invoke($logger)
    {
        $logger->pushProcessor(function ($record) {
            $record['extra']['auth_id'] = Auth::id() ?? 'system';
            $record['extra']['auth_email'] = Auth::user()->email ?? 'system';
            $record['extra']['url'] = request()->fullUrl() ?? null;
            $record['extra']['method'] = request()->method() ?? null;
            $record['extra']['request'] = collect(request()->except('transaction_id'))->all() ?? null;
            $record['extra']['controller'] = request()->route() ? request()->route()->getAction('controller') : null;
            $record['extra']['ip'] = request()->ip() ?? null;
            $record['extra']['user_agent'] = request()->userAgent() ?? null;
            $record['extra']['transaction_id'] = request()->attributes->get('transaction_id') ?? null;
            return $record;
        });
    }
}
