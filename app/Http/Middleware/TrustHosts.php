<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;
use Illuminate\Http\Request;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        return [
            // Trust the main application URL and its subdomains
            $this->allSubdomainsOfApplicationUrl(),
            // Explicitly trust the tenant wildcard domain pattern
            '*.collaborinbox.test',
        ];
    }
}
