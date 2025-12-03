<?php

namespace App\Validation;

class CustomRules
{
    /**
     * Check if date is today or in the future
     * 
     * @param string|null $value The date value to check
     * @param string|null $params Not used
     * @param array $data All form data
     * @param string|null $error Error message (passed by reference)
     * @return bool
     */
    public function check_future_date(?string $value = null, ?string $params = null, array $data = [], ?string &$error = null): bool
    {
        // Allow empty/null dates
        if (empty($value)) {
            return true;
        }
        
        // Validate date format
        if (!strtotime($value)) {
            $error = 'Invalid date format.';
            return false;
        }
        
        $inputDate = strtotime($value);
        $today = strtotime(date('Y-m-d'));
        
        if ($inputDate < $today) {
            $error = 'The date must be today or a future date.';
            return false;
        }
        
        return true;
    }

    /**
     * Check if start_date is not after end_date
     * 
     * @param string|null $value The start_date value
     * @param string|null $params The field name to compare with (end_date)
     * @param array $data All form data
     * @param string|null $error Error message (passed by reference)
     * @return bool
     */
    public function check_date_order(?string $value = null, ?string $params = null, array $data = [], ?string &$error = null): bool
    {
        // If start_date is empty, validation passes
        if (empty($value)) {
            return true;
        }
        
        // Get the end_date from form data
        $endDate = $data[$params] ?? null;
        
        // If end_date is empty, validation passes
        if (empty($endDate)) {
            return true;
        }
        
        // Validate both date formats
        if (!strtotime($value) || !strtotime($endDate)) {
            $error = 'Invalid date format.';
            return false;
        }
        
        $startTimestamp = strtotime($value);
        $endTimestamp = strtotime($endDate);
        
        // Check if start_date is after end_date
        if ($startTimestamp > $endTimestamp) {
            $error = 'Start date cannot be after end date.';
            return false;
        }
        
        return true;
    }

    /**
     * Check if enrollment dates are within term dates
     * 
     * @param string|null $value The enrollment date value
     * @param string|null $params Contains "start_date,end_date" field names
     * @param array $data All form data
     * @param string|null $error Error message (passed by reference)
     * @return bool
     */
    public function check_enrollment_within_term(?string $value = null, ?string $params = null, array $data = [], ?string &$error = null): bool
    {
        // If enrollment date is empty, validation passes
        if (empty($value)) {
            return true;
        }
        
        // Parse params to get term start and end field names
        $paramArray = explode(',', $params);
        $termStartField = $paramArray[0] ?? 'start_date';
        $termEndField = $paramArray[1] ?? 'end_date';
        
        $termStart = $data[$termStartField] ?? null;
        $termEnd = $data[$termEndField] ?? null;
        
        // If term dates are empty, validation passes (optional term dates)
        if (empty($termStart) || empty($termEnd)) {
            return true;
        }
        
        // Validate date formats
        if (!strtotime($value) || !strtotime($termStart) || !strtotime($termEnd)) {
            $error = 'Invalid date format.';
            return false;
        }
        
        $enrollmentTimestamp = strtotime($value);
        $termStartTimestamp = strtotime($termStart);
        $termEndTimestamp = strtotime($termEnd);
        
        // Check if enrollment date is before term start
        if ($enrollmentTimestamp < $termStartTimestamp) {
            $error = 'Enrollment date should be on or after the term start date.';
            return false;
        }
        
        // Check if enrollment date is after term end
        if ($enrollmentTimestamp > $termEndTimestamp) {
            $error = 'Enrollment date should be on or before the term end date.';
            return false;
        }
        
        return true;
    }
}
