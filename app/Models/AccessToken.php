<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccessToken extends Model
{
    protected $fillable = [
        'token',
        'expires_at',
        'assigned_to',
        'scope',
        'assignee_email',
        'last_used_at',
        'is_active',
    ];

    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'expires_at' => 'datetime',
        'scope' => 'array',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public static function generateNew()
    {
        return (string) Str::uuid();
    }

    /**
     * Regenrate the access token.
     * @return AccessToken
     */
    public function regenerate(): self
    {
        $this->token = static::generateNew();
        $this->save();

        return $this;
    }

    /**
     * Check whether or not the access token is expired.
     * @return bool
     */
    private function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Validate the access token.
     * @param array $scope The required scope(s) to validate against.
     * @return bool Whether or not the access token is valid for the given scope(s).
     */
    public function validate(array $scope = []): bool
    {
        $isValid = true;

        if (!$this->is_active) {
            $isValid = false;
        }

        if ($isValid && $this->isExpired()) {
            $isValid = false;
        }

        if ($isValid && !empty($scope) && !in_array('*', $scope)) {
            $isValid = collect($scope)->contains(fn($requiredScope) => in_array($requiredScope, $this->scope));
        }

        if ($isValid) {
            $this->last_used_at = now();
            $this->save();
        }

        return $isValid;
    }

    /**
     * Check whether or not the token has access to the given scope.
     * @param string $scope The scope to check.
     * @return bool Whether or not the token has access to the given scope.
     */
    public function hasScope(string $scope): bool
    {
        if (in_array('*', $this->scope)) {
            return true;
        }
        return in_array($scope, $this->scope);
    }
}