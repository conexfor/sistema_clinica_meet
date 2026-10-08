<?php

namespace Tests\Feature;

use App\Services\Admin\ReportPeriod;
use Tests\TestCase;

class ReportDateFilterTest extends TestCase
{
    public function test_custom_date_range_is_supported_for_reports(): void
    {
        $range = ReportPeriod::make('custom', '2024-02-01', '2024-02-15');

        $this->assertSame('custom', $range->key);
        $this->assertSame('2024-02-01', $range->start->toDateString());
        $this->assertSame('2024-02-15', $range->end->toDateString());
        $this->assertSame('01/02/2024 - 15/02/2024', $range->label);
    }
}
