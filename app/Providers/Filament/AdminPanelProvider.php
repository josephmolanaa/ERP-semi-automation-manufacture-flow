<?php
// app/Providers/Filament/AdminPanelProvider.php (tambahkan ke widgets())

->widgets([
    \App\Filament\Widgets\CncFlowStatsWidget::class,
    \App\Filament\Widgets\JobOrderChartWidget::class,
])