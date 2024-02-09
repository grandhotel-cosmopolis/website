<?php

namespace Tests\Unit\Services;

use App\Services\TimeService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Tests\TestCase;

class TimeServiceTest extends TestCase
{
    private TimeService $cut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cut = new TimeService();
    }

    /** @test */
    public function isTime_invalidString_returnsFalse() {
        // Act
        $result = $this->cut->isTime('invalid');

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function isTime_null_returnsFalse() {
        // Act
        $result = $this->cut->isTime(null);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function isTime_invalidObject_returnsFalse() {
        // Act
        $result = $this->cut->isTime(['bla' => 'bla']);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function isTime_valid_returnsTrue() {
        // Act
        $result = $this->cut->isTime(Carbon::now());

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function isTime_validTimeString_returnsTrue() {
        // Act
        $result = $this->cut->isTime('2024-02-11T18:00:00.000Z');

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function validateTimeRange_invalid_returnsFalse()
    {
        // Act
        $result = $this->cut->validateTimeRange(Carbon::now(), Carbon::now()->subHour());

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function validateTimeRange_valid_returnTrue()
    {
        // Act
        $result = $this->cut->validateTimeRange(Carbon::now(), Carbon::now()->addHour());

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function validateTimeRange_equalTimes_returnsFalse()
    {
        // Arrange
        $now = Carbon::now();

        // Act
        $result = $this->cut->validateTimeRange($now, $now);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function updateTimeForEveryXDays_allValid_returnsCorrectDates() {
        // Arrange
        $start = Carbon::parse('2024-02-11T18:00:00.000Z');
        $end = Carbon::parse('2024-02-11T20:00:00.000Z');

        // Act
        $result = $this->cut->updateTimesForEveryXDays($start, $end, 2);

        // Assert
        $this->assertEquals($start->day + 2, $result[0]->day);
        $this->assertEquals($start->year, $result[0]->year);
        $this->assertEquals($start->month, $result[0]->month);
        $this->assertEquals($start->hour, $result[0]->hour);
        $this->assertEquals($start->minute, $result[0]->minute);

        $this->assertEquals($end->day + 2, $result[1]->day);
        $this->assertEquals($end->year, $result[1]->year);
        $this->assertEquals($end->month, $result[1]->month);
        $this->assertEquals($end->hour, $result[1]->hour);
        $this->assertEquals($end->minute, $result[1]->minute);
    }

    /** @test */
    public function updateTimeForEveryXDays_overMonth_returnsCorrectDates() {
        // Arrange
        $start = Carbon::parse('2024-02-25T18:00:00.000Z');
        $end = Carbon::parse('2024-02-25T20:00:00.000Z');

        // Act
        $result = $this->cut->updateTimesForEveryXDays($start, $end, 12);

        // Assert
        $this->assertEquals(8, $result[0]->day);
        $this->assertEquals($start->year, $result[0]->year);
        $this->assertEquals($start->month + 1, $result[0]->month);
        $this->assertEquals($start->hour, $result[0]->hour);
        $this->assertEquals($start->minute, $result[0]->minute);

        $this->assertEquals(8, $result[1]->day);
        $this->assertEquals($end->year, $result[1]->year);
        $this->assertEquals($end->month + 1, $result[1]->month);
        $this->assertEquals($end->hour, $result[1]->hour);
        $this->assertEquals($end->minute, $result[1]->minute);
    }

    /** @test */
    public function updateTimeForEveryXDays_simple_inputIsNotModified() {
        // Arrange
        $start = Carbon::parse('2024-02-10T18:00:00.000Z');
        $originalStart = $start->clone();
        $end = Carbon::parse('2024-02-10T20:00:00.000Z');
        $originalEnd = $end->clone();

        // Act
        $this->cut->updateTimesForEveryXDays($start, $end, 1);

        // Assert
        $this->assertEquals($originalStart, $start);
        $this->assertEquals($originalEnd, $end);
    }

    /** @test */
    public function updateTimeForEveryXDays_overYear_returnsCorrectDates() {
        // Arrange
        $start = Carbon::parse('2024-12-31T18:00:00.000Z');
        $end = Carbon::parse('2024-12-31T20:00:00.000Z');

        // Act
        $result = $this->cut->updateTimesForEveryXDays($start, $end, 12);

        // Assert
        $this->assertEquals(12, $result[0]->day);
        $this->assertEquals($start->year + 1, $result[0]->year);
        $this->assertEquals(1, $result[0]->month);
        $this->assertEquals($start->hour, $result[0]->hour);
        $this->assertEquals($start->minute, $result[0]->minute);

        $this->assertEquals(12, $result[1]->day);
        $this->assertEquals($end->year + 1, $result[1]->year);
        $this->assertEquals(1, $result[1]->month);
        $this->assertEquals($end->hour, $result[1]->hour);
        $this->assertEquals($end->minute, $result[1]->minute);
    }

    /** @test */
    public function updateTimesForEveryMonthAtDayX_lastJanuary_correctDataAreReturned() {
        // Arrange
        $start = Carbon::parse('2024-01-31T18:00:00.000Z');
        $end = Carbon::parse('2024-01-31T20:00:00.000Z');

        // Act
        $result = $this->cut->updateTimesForEveryMonthAtDayX($start, $end, 31);

        // Assert
        $this->assertEquals(29, $result[0]->day);
        $this->assertEquals(2, $result[0]->month);
        $this->assertEquals($start->hour, $result[0]->hour);
        $this->assertEquals($start->minute, $result[0]->minute);
        $this->assertEquals($start->year, $result[0]->year);
        $this->assertEquals(29, $result[1]->day);
        $this->assertEquals(2, $result[1]->month);
        $this->assertEquals($end->hour, $result[1]->hour);
        $this->assertEquals($end->minute, $result[1]->minute);
        $this->assertEquals($end->year, $result[1]->year);
    }

    /** @test */
    public function updateTimesForEveryMonthAtDayX_simple_correctDataAreReturned() {
        // Arrange
        $start = Carbon::parse('2024-01-10T18:00:00.000Z');
        $end = Carbon::parse('2024-01-10T20:00:00.000Z');

        // Act
        $result = $this->cut->updateTimesForEveryMonthAtDayX($start, $end, 10);

        // Assert
        $this->assertEquals(10, $result[0]->day);
        $this->assertEquals(2, $result[0]->month);
        $this->assertEquals($start->hour, $result[0]->hour);
        $this->assertEquals($start->minute, $result[0]->minute);
        $this->assertEquals($start->year, $result[0]->year);
        $this->assertEquals(10, $result[1]->day);
        $this->assertEquals(2, $result[1]->month);
        $this->assertEquals($end->hour, $result[1]->hour);
        $this->assertEquals($end->minute, $result[1]->minute);
        $this->assertEquals($end->year, $result[1]->year);
    }

    /** @test */
    public function updateTimesForEveryMonthAtDayX_overMonth_inputIsNotModified() {
        // Arrange
        $start = Carbon::parse('2024-01-31T18:00:00.000Z');
        $originalStart = $start->clone();
        $end = Carbon::parse('2024-01-31T20:00:00.000Z');
        $originalEnd = $end->clone();

        // Act
        $this->cut->updateTimesForEveryMonthAtDayX($start, $end, 31);

        // Assert
        $this->assertEquals($originalStart, $start);
        $this->assertEquals($originalEnd, $end);
    }

    /** @test */
    public function updateTimesForEveryMonthAtDayX_simple_inputIsNotModified() {
        // Arrange
        $start = Carbon::parse('2024-01-10T18:00:00.000Z');
        $originalStart = $start->clone();
        $end = Carbon::parse('2024-01-10T20:00:00.000Z');
        $originalEnd = $end->clone();

        // Act
        $this->cut->updateTimesForEveryMonthAtDayX($start, $end, 10);

        // Assert
        $this->assertEquals($originalStart, $start);
        $this->assertEquals($originalEnd, $end);
    }

    /** @test */
    public function getTime_valid_returnsCorrectData() {
        // Arrange
        $data = Carbon::parse('2024-01-10T18:00:00.000Z');

        // Act
        $result = $this->cut->getTime($data);

        // Assert
        $this->assertEquals(18, $result[0]);
        $this->assertEquals(0, $result[1]);
        $this->assertEquals(0, $result[2]);
        $this->assertEquals(0, $result[3]);
    }

    /** @test */
    public function updateTimesForEveyLastDayInMonth_valid_inputIsNotModified() {
        // Arrange
        $start = Carbon::parse('2024-01-10T18:00:00.000Z');
        $originalStart = $start->clone();
        $end = Carbon::parse('2024-01-10T20:00:00.000Z');
        $originalEnd = $end->clone();

        // Act
        $this->cut->updateTimesForEveyLastDayInMonth($start, $end, CarbonInterface::MONDAY);

        // Assert
        $this->assertEquals($originalStart, $start);
        $this->assertEquals($originalEnd, $end);
    }

    /** @test */
    public function updateTimesForEveyLastDayInMonth_valid_correctDataAreReturned() {
        // Arrange
        $start = Carbon::parse('2024-01-31T18:00:00.000Z');
        $end = Carbon::parse('2024-01-31T20:00:00.000Z');

        // Act
        $result = $this->cut->updateTimesForEveyLastDayInMonth($start, $end, CarbonInterface::WEDNESDAY);

        // Assert
        $this->assertEquals(28, $result[0]->day);
        $this->assertEquals(2, $result[0]->month);
        $this->assertEquals($start->hour, $result[0]->hour);
        $this->assertEquals($start->minute, $result[0]->minute);
        $this->assertEquals($start->year, $result[0]->year);
        $this->assertEquals(28, $result[1]->day);
        $this->assertEquals(2, $result[1]->month);
        $this->assertEquals($end->hour, $result[1]->hour);
        $this->assertEquals($end->minute, $result[1]->minute);
        $this->assertEquals($end->year, $result[1]->year);
    }

    /** @test */
    public function updateTimesForEveryFirstDayInMonth_valid_inputIsNotModified() {
        // Arrange
        $start = Carbon::parse('2024-01-01T18:00:00.000Z');
        $originalStart = $start->clone();
        $end = Carbon::parse('2024-01-01T20:00:00.000Z');
        $originalEnd = $end->clone();

        // Act
        $this->cut->updateTimesForEveryFirstDayInMonth($start, $end, CarbonInterface::MONDAY);

        // Assert
        $this->assertEquals($originalStart, $start);
        $this->assertEquals($originalEnd, $end);
    }

    /** @test */
    public function updateTimesForEveryFirstDayInMonth_valid_correctDataAreReturned() {
        // Arrange
        $start = Carbon::parse('2024-01-01T18:00:00.000Z');
        $end = Carbon::parse('2024-01-01T20:00:00.000Z');

        // Act
        $result = $this->cut->updateTimesForEveryFirstDayInMonth($start, $end, CarbonInterface::MONDAY);

        // Assert
        $this->assertEquals(5, $result[0]->day);
        $this->assertEquals(2, $result[0]->month);
        $this->assertEquals($start->hour, $result[0]->hour);
        $this->assertEquals($start->minute, $result[0]->minute);
        $this->assertEquals($start->year, $result[0]->year);
        $this->assertEquals(5, $result[1]->day);
        $this->assertEquals(2, $result[1]->month);
        $this->assertEquals($end->hour, $result[1]->hour);
        $this->assertEquals($end->minute, $result[1]->minute);
        $this->assertEquals($end->year, $result[1]->year);
    }

    /** @test */
    public function updateTimesForEveryThirdDayInMonth_valid_inputIsNotModified() {
        // Arrange
        $start = Carbon::parse('2024-01-15T18:00:00.000Z');
        $originalStart = $start->clone();
        $end = Carbon::parse('2024-01-15T20:00:00.000Z');
        $originalEnd = $end->clone();

        // Act
        $this->cut->updateTimesForEveryThirdDayInMonth($start, $end, CarbonInterface::MONDAY);

        // Assert
        $this->assertEquals($originalStart, $start);
        $this->assertEquals($originalEnd, $end);
    }

    /** @test */
    public function updateTimesForEveryThirdDayInMonth_valid_correctDataAreReturned() {
        // Arrange
        $start = Carbon::parse('2024-01-15T18:00:00.000Z');
        $end = Carbon::parse('2024-01-15T20:00:00.000Z');

        // Act
        $result = $this->cut->updateTimesForEveryThirdDayInMonth($start, $end, CarbonInterface::MONDAY);

        // Assert
        $this->assertEquals(19, $result[0]->day);
        $this->assertEquals(2, $result[0]->month);
        $this->assertEquals($start->hour, $result[0]->hour);
        $this->assertEquals($start->minute, $result[0]->minute);
        $this->assertEquals($start->year, $result[0]->year);
        $this->assertEquals(19, $result[1]->day);
        $this->assertEquals(2, $result[1]->month);
        $this->assertEquals($end->hour, $result[1]->hour);
        $this->assertEquals($end->minute, $result[1]->minute);
        $this->assertEquals($end->year, $result[1]->year);
    }

    /** @test */
    public function updateTimesForEverySecondDayInMonth_valid_inputIsNotModified() {
        // Arrange
        $start = Carbon::parse('2024-01-08T18:00:00.000Z');
        $originalStart = $start->clone();
        $end = Carbon::parse('2024-01-08T20:00:00.000Z');
        $originalEnd = $end->clone();

        // Act
        $this->cut->updateTimesForEverySecondDayInMonth($start, $end, CarbonInterface::MONDAY);

        // Assert
        $this->assertEquals($originalStart, $start);
        $this->assertEquals($originalEnd, $end);
    }

    /** @test */
    public function updateTimesForEverySecondDayInMonth_valid_correctDataAreReturned() {
        // Arrange
        $start = Carbon::parse('2024-01-08T18:00:00.000Z');
        $end = Carbon::parse('2024-01-08T20:00:00.000Z');

        // Act
        $result = $this->cut->updateTimesForEverySecondDayInMonth($start, $end, CarbonInterface::MONDAY);

        // Assert
        $this->assertEquals(12, $result[0]->day);
        $this->assertEquals(2, $result[0]->month);
        $this->assertEquals($start->hour, $result[0]->hour);
        $this->assertEquals($start->minute, $result[0]->minute);
        $this->assertEquals($start->year, $result[0]->year);
        $this->assertEquals(12, $result[1]->day);
        $this->assertEquals(2, $result[1]->month);
        $this->assertEquals($end->hour, $result[1]->hour);
        $this->assertEquals($end->minute, $result[1]->minute);
        $this->assertEquals($end->year, $result[1]->year);
    }
}
