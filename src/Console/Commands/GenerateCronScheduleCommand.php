<?php

namespace Marshmallow\Commands\Console\Commands;

use Exception;
use ArgumentCountError;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Marshmallow\HelperFunctions\Facades\Arrayable;
use Illuminate\Console\Scheduling\ManagesFrequencies;

class GenerateCronScheduleCommand extends Command
{
    use ManagesFrequencies;

    /**
     * This will store all the possible cron expressions.
     * After this array is filled, we will generate one
     * single line to add in the crontab.
     *
     * @var array
     */
    protected $cron_expressions = [];

    protected $minutes = [];
    protected $hour = [];
    protected $day_month = [];
    protected $month = [];
    protected $day_week = [];

    /**
     * All the possible options currently available in
     * Laravel's scheduler.
     *
     * @var array
     */
    protected $frequency_options = [
        'cron',
        'everyMinute',
        'everyTwoMinutes',
        'everyThreeMinutes',
        'everyFourMinutes',
        'everyFiveMinutes',
        'everyTenMinutes',
        'everyFifteenMinutes',
        'everyThirtyMinutes',
        'hourly',
        'hourlyAt',
        'everyTwoHours',
        'everyThreeHours',
        'everyFourHours',
        'everySixHours',
        'daily',
        'dailyAt',
        'twiceDaily',
        'weekly',
        'weeklyOn',
        'monthly',
        'monthlyOn',
        'twiceMonthly',
        'lastDayOfMonth',
        'quarterly',
        'yearly',
        'yearlyOn',
    ];

    /**
     * The cron expression representing the event's frequency.
     *
     * @var string
     */
    public $expression = '* * * * *';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all cache clear artisan commands from Laravel';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $kernel_content = $this->getConsoleKernelContent();

        foreach ($this->frequency_options as $frequency) {
            $match_found = preg_match_all("/$frequency\((.*?)\)/", $kernel_content, $matches);

            /**
             * If this frequency is not found, we can continue.
             */
            if (! $match_found) {
                continue;
            }


            /**
             * We have matches, we now loop through them and get the corresponding
             * cron expression.
             * @var string $match
             */
            foreach ($matches[0] as $key => $match) {

                /**
                 * Reset the params parameter to an empty array.
                 */
                $params = [];

                /**
                 * If we have parameters in the regex result, we handle them here.
                 */
                if (isset($matches[1]) && isset($matches[1][$key])) {

                    if ($frequency === 'cron') {
                        $params = $matches[1][$key];
                    } else {
                        $params = collect(explode(',', $matches[1][$key]))->map(function ($param) {
                            /**
                             * Trim the parameter so we get rid of all
                             * remaining spaces.
                             */
                            $param = trim($param);

                            /**
                             * If the parameter has a single or double quote in them,
                             * they must be a string, so we remove them and return
                             * the string value.
                             */
                            if (strrpos($param, '"') !== false || strpos($param, "'") !== false) {
                                return str_replace(['"', "'"], '', $param);
                            } else {
                                /**
                                 * If no single or double quote is found, this must be
                                 * a type of integer. It could also be a variable, but
                                 * its almost Christmas, i don't want to build that in
                                 * at this point.
                                 */
                                return intval($param);
                            }
                        });
                    }
                }

                /**
                 * Build the cron expression and add it
                 * to our temporary array.
                 */
                $this->addCronScheduleFromFrequencyMethod($frequency, $params);
            }
        }

        /**
         * This will explode the expressions and add
         * them to there corresponding arrays like; $minutes, $hours etc.
         */
        $test = $this->explodeExpressionsToSingleArrays();

        dd($test);

        $schedule = $this->buildSchedule();

        dd($schedule);
    }

    protected function getConsoleKernelContent(): string
    {
        /**
         * Build an absolute path to the general location
         * of the console kernel.
         */
        $kernel_file_path = app_path('Console/Kernel.php');

        /**
         * If this file doesn't exist, we throw an exception.
         */
        if (! file_exists($kernel_file_path)) {
            throw new Exception("No Kernel.php found");
        }

        /**
         * Return the contents of Console/Kernel.php
         */
        return file_get_contents($kernel_file_path);
    }

    /**
     * Run the method that comes from the Laravel
     * ManagesFrequencies trait and store it's
     * result in our temporary cron expressions array.
     */
    protected function addCronScheduleFromFrequencyMethod($method, $params): void
    {
        /**
         * Make sure params is an array.
         */
        $params = ($params instanceof Collection) ? $params : collect([$params]);
        $expression = $this->$method(... $params)->expression;

        $expression = ltrim($expression, "'");
        $expression = rtrim($expression, "'");

        $this->cron_expressions[] = $expression;
        $this->expression = '* * * * *';
    }

    /**
     * This will explode the expressions and add
     * them to there corresponding arrays;
     * $minutes, $hours, $day_month, $month, $day_week
     */
    protected function explodeExpressionsToSingleArrays()
    {
        foreach ($this->cron_expressions as $expression) {
            $expression_parts = explode(' ', $expression);
            $this->addHours($expression_parts[0], 60);
            $this->addHours($expression_parts[1], 24);
            $this->addDaysOfMonth($expression_parts[2]);
            $this->addMonths($expression_parts[3]);
            $this->addDaysOfWeek($expression_parts[4]);
        }

        $minutes = $this->cleanScheduleItemArray($this->minutes, 60);
        $minutes = $this->translateNumbersToCronExpression($minutes, 60);

        $hours = $this->translateNumbersToCronExpression(
            $this->cleanScheduleItemArray($this->hour, 24)
        , 24);

        $days_month = $this->translateNumbersToCronExpression(
            $this->cleanScheduleItemArray($this->day_month, 31)
        , 31);

        $months = $this->translateNumbersToCronExpression(
            $this->cleanScheduleItemArray($this->month, 12)
        , 12);

        $days_week = $this->translateNumbersToCronExpression(
            $this->cleanScheduleItemArray($this->day_week, 7)
        , 7);

        return join(' ', [
            $minutes,
            $hours,
            $days_month,
            $months,
            $days_week,
        ]);
    }

    protected function translateNumbersToCronExpression($items, $max_results)
    {
        /**
         * Check if all are avaiable.
         */
        if (count($items) == $max_results) {
            return '*';
        }

        if (count($items) === 1) {
            return $items[0];
        }

        /**
         * Check if a multiplier like 3/5 is available.
         */

        $muliplier = null;
        for ($i=0; $i < $max_results; $i++) {
            if ($i <= 1) {
                continue;
            }
            if (Arrayable::allItemsAreMultipleOf($i, $items)) {
                $muliplier = $i;
            }
        }

        if ($muliplier) {
            return "*/$muliplier";
        }

        /**
         * Check for ranges.
         */
        $grouped = Arrayable::groupNumbers($items);
        return join(',', $grouped);
    }

    protected function cleanScheduleItemArray($array, $max_results)
    {
        $key = array_search('*', $array);

        if ($key && $array[$key] === '*') {
            $array = [];
            for ($i=0; $i < $max_results; $i++) {
                $array[] = $i;
            }
            return $array;
        }

        $array = collect($array)->map(function ($item) {
            return intval($item);
        })->toArray();

        return $array;
    }

    protected function addMinutes($setting)
    {
        $settings = $this->getCommaSeperatedExpressions($setting);
        foreach ($settings as $setting) {
            $this->minutes[] = $setting;
        }
        return $setting;
    }
    protected function addHours($setting, $max_results)
    {
        $expressions = $this->getCommaSeperatedExpressions($setting);
        foreach ($expressions as $expression) {
            $settings = $this->getDashSeperatedRanges($expression, $max_results);
            foreach ($settings as $setting) {
                if (in_array($setting, $this->hour)) {
                    continue;
                }
                $this->hour[] = $setting;
            }
        }
        return $setting;
    }
    protected function addDaysOfMonth($setting)
    {
        $settings = $this->getCommaSeperatedExpressions($setting);
        foreach ($settings as $setting) {
            $this->day_month[] = $setting;
        }
        return $setting;
    }
    protected function addMonths($setting)
    {
        $settings = $this->getCommaSeperatedExpressions($setting);
        foreach ($settings as $setting) {
            $this->month[] = $setting;
        }
        return $setting;
    }
    protected function addDaysOfWeek($setting)
    {
        $settings = $this->getCommaSeperatedExpressions($setting);
        foreach ($settings as $setting) {
            $this->day_week[] = $setting;
        }
        return $setting;
    }

    protected function getCommaSeperatedExpressions($setting)
    {
        $settings = explode(',', $setting);
        return $settings;
    }

    protected function getDashSeperatedRanges($expression, $max_results)
    {
        $return_range = [];
        $range = explode('-', $expression);
        if (count($range) > 1) {
            for ($i=$range[0]; $i <= $range[1]; $i++) {
                $return_range[] = $i;
            }
        }
        $range = explode('/', $expression);
        if (count($range) > 1) {
            $start = ($range[0] == '*') ? 0 : $range[0];
            $steps = intval($range[1]);
            $marker = $start;
            while(true) {
                $return_range[] = $marker;
                $marker+= $steps;
                if ($marker >= $max_results) {
                    break;
                }
            }
        }
        if (empty($return_range)) {
            $return_range[] = $expression;
        }

        return $return_range;
    }

    protected function buildSchedule()
    {
        $schedule = [
            $this->convertArrayToSchedule($this->minutes),
            $this->convertArrayToSchedule($this->hour),
            $this->convertArrayToSchedule($this->day_month),
            $this->convertArrayToSchedule($this->month),
            $this->convertArrayToSchedule($this->day_week),
        ];

        return join(' ', $schedule);
    }

    protected function convertArrayToSchedule($schedule)
    {
        if (empty($schedule)) {
            return 0;
        }

        if (in_array('*', $schedule)) {
            return '*';
        }

        return join('/', $schedule);
    }
}
