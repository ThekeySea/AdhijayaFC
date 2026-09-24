<?php

namespace Tests;

use App\Models\BusinessSetting;
use App\Services\OpeningHours;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        BusinessSetting::flushCurrent();
        OpeningHours::flush();
    }
}
