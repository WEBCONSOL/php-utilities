<?php

namespace WC\Utilities;

class Date extends \DateTime
{
    const DAY_ABBR = "\x021\x03";
    const DAY_NAME = "\x022\x03";
    const MONTH_ABBR = "\x023\x03";
    const MONTH_NAME = "\x024\x03";

    public static $format = 'Y-m-d H:i:s';
    protected static $gmt;
    protected static $stz;
    protected $tz;

    public function __construct($date = 'now', $tz = null)
    {
        // Create the base GMT and server time zone objects.
        if (empty(self::$gmt) || empty(self::$stz))
        {
            self::$gmt = new \DateTimeZone('GMT');
            self::$stz = new \DateTimeZone(@date_default_timezone_get());
        }

        // If the time zone object is not set, attempt to build it.
        if (!($tz instanceof \DateTimeZone))
        {
            if ($tz === null)
            {
                $tz = self::$gmt;
            }
            elseif (is_string($tz))
            {
                $tz = new \DateTimeZone($tz);
            }
        }

        // If the date is numeric assume a unix timestamp and convert it.
        \date_default_timezone_set('UTC');
        $date = is_numeric($date) ? date('c', $date) : $date;

        // Call the DateTime constructor.
        parent::__construct($date, $tz);

        // Reset the timezone for 3rd party libraries/extension that does not use JDate
        \date_default_timezone_set(self::$stz->getName());

        // Set the timezone object for access later.
        $this->tz = $tz;
    }

    public function __get($name)
    {
        $value = null;

        switch ($name)
        {
            case 'daysinmonth':
                $value = $this->format('t', true);
                break;

            case 'dayofweek':
                $value = $this->format('N', true);
                break;

            case 'dayofyear':
                $value = $this->format('z', true);
                break;

            case 'isleapyear':
                $value = (boolean) $this->format('L', true);
                break;

            case 'day':
                $value = $this->format('d', true);
                break;

            case 'hour':
                $value = $this->format('H', true);
                break;

            case 'minute':
                $value = $this->format('i', true);
                break;

            case 'second':
                $value = $this->format('s', true);
                break;

            case 'month':
                $value = $this->format('m', true);
                break;

            case 'ordinal':
                $value = $this->format('S', true);
                break;

            case 'week':
                $value = $this->format('W', true);
                break;

            case 'year':
                $value = $this->format('Y', true);
                break;

            default:
                $trace = debug_backtrace();
                trigger_error(
                    'Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
                    E_USER_NOTICE
                );
        }

        return $value;
    }

    public function __toString()
    {
        return (string) parent::format(self::$format);
    }

    public static function getInstance($date = 'now', $tz = null)
    {
        return new Date($date, $tz);
    }

    public function dayToString($day, $abbr = false)
    {
        switch ($day)
        {
            case 0:
                return $abbr ? 'SUN' : 'SUNDAY';
            case 1:
                return $abbr ? 'MON' : 'MONDAY';
            case 2:
                return $abbr ? 'TUE' : 'TUESDAY';
            case 3:
                return $abbr ? 'WED' : 'WEDNESDAY';
            case 4:
                return $abbr ? 'THU' : 'THURSDAY';
            case 5:
                return $abbr ? 'FRI' : 'FRIDAY';
            case 6:
                return $abbr ? 'SAT' : 'SATURDAY';
        }
    }

    public function calendar($format, $local = false, $translate = true)
    {
        return $this->format($format, $local, $translate);
    }

    public function format($format, $local = false, $translate = true)
    {
        if ($translate)
        {
            // Do string replacements for date format options that can be translated.
            $format = preg_replace('/(^|[^\\\])D/', "\\1" . self::DAY_ABBR, $format);
            $format = preg_replace('/(^|[^\\\])l/', "\\1" . self::DAY_NAME, $format);
            $format = preg_replace('/(^|[^\\\])M/', "\\1" . self::MONTH_ABBR, $format);
            $format = preg_replace('/(^|[^\\\])F/', "\\1" . self::MONTH_NAME, $format);
        }

        // If the returned time should not be local use GMT.
        if ($local == false && !empty(self::$gmt))
        {
            parent::setTimezone(self::$gmt);
        }

        // Format the date.
        $return = parent::format($format);

        if ($translate)
        {
            // Manually modify the month and day strings in the formatted time.
            if (strpos($return, self::DAY_ABBR) !== false)
            {
                $return = str_replace(self::DAY_ABBR, $this->dayToString(parent::format('w'), true), $return);
            }

            if (strpos($return, self::DAY_NAME) !== false)
            {
                $return = str_replace(self::DAY_NAME, $this->dayToString(parent::format('w')), $return);
            }

            if (strpos($return, self::MONTH_ABBR) !== false)
            {
                $return = str_replace(self::MONTH_ABBR, $this->monthToString(parent::format('n'), true), $return);
            }

            if (strpos($return, self::MONTH_NAME) !== false)
            {
                $return = str_replace(self::MONTH_NAME, $this->monthToString(parent::format('n')), $return);
            }
        }

        if ($local == false && !empty($this->tz))
        {
            parent::setTimezone($this->tz);
        }

        return $return;
    }

    public function getOffsetFromGmt($hours = false)
    {
        return (float) $hours ? ($this->tz->getOffset($this) / 3600) : $this->tz->getOffset($this);
    }

    public function monthToString($month, $abbr = false)
    {
        switch ($month)
        {
            case 1:
                return $abbr ? 'JANUARY_SHORT' : 'JANUARY';
            case 2:
                return $abbr ? 'FEBRUARY_SHORT' : 'FEBRUARY';
            case 3:
                return $abbr ? 'MARCH_SHORT' : 'MARCH';
            case 4:
                return $abbr ? 'APRIL_SHORT' : 'APRIL';
            case 5:
                return $abbr ? 'MAY_SHORT' : 'MAY';
            case 6:
                return $abbr ? 'JUNE_SHORT' : 'JUNE';
            case 7:
                return $abbr ? 'JULY_SHORT' : 'JULY';
            case 8:
                return $abbr ? 'AUGUST_SHORT' : 'AUGUST';
            case 9:
                return $abbr ? 'SEPTEMBER_SHORT' : 'SEPTEMBER';
            case 10:
                return $abbr ? 'OCTOBER_SHORT' : 'OCTOBER';
            case 11:
                return $abbr ? 'NOVEMBER_SHORT' : 'NOVEMBER';
            case 12:
                return $abbr ? 'DECEMBER_SHORT' : 'DECEMBER';
        }
    }

    public function setTimezone($tz)
    {
        $this->tz = $tz;

        return parent::setTimezone($tz);
    }

    public function toISO8601($local = false)
    {
        return $this->format(\DateTime::RFC3339, $local, false);
    }

    public function toSql($local = false)
    {
        return $this->format(self::$format, $local, false);
    }

    public function toRFC822($local = false)
    {
        return $this->format(DateTime::RFC2822, $local, false);
    }

    public function toUnix()
    {
        return (int) parent::format('U');
    }
}
