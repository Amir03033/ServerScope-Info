@if (config('serverscope.staging.enabled'))
    <div role="note" style="background:#b91c1c;color:#fff;font:600 13px/1.4 Inter,system-ui,sans-serif;padding:6px 16px;text-align:center">
        STAGING — niet openbaar, niet geïndexeerd.@if (app()->isProduction()) Let op: STAGING staat aan terwijl APP_ENV=production.@endif
    </div>
@endif
