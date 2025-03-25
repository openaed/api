<?php

if (!function_exists('isValidUuid')) {
    /**
     * Check if a given string is a valid UUID
     * 
     * @param   mixed  $uuid   The string to check
     * @return  boolean
     */
    function isValidUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid);
    }
}