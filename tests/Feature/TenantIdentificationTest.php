<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Services\TenantResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIdentificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_extract_subdomain_from_request()
    {
        // Arrange
        $request = Request::create('http://test-tenant.collaborinbox.test/some/path');
        $resolver = new TenantResolver();

        // Act
        $subdomain = $resolver->extractSubdomain($request);

        // Assert
        $this->assertEquals('test-tenant', $subdomain);
    }

    /** @test */
    public function it_returns_null_subdomain_for_www()
    {
        // Arrange
        $request = Request::create('http://www.collaborinbox.test/some/path');
        $resolver = new TenantResolver();

        // Act
        $subdomain = $resolver->extractSubdomain($request);

        // Assert
        $this->assertNull($subdomain);
    }

    /** @test */
    public function it_returns_null_subdomain_for_different_domain()
    {
        // Arrange
        $request = Request::create('http://example.com/some/path');
        $resolver = new TenantResolver();

        // Act
        $subdomain = $resolver->extractSubdomain($request);

        // Assert
        $this->assertNull($subdomain);
    }

    /** @test */
    public function it_can_find_tenant_by_subdomain()
    {
        // Arrange
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $wildcardDomain = config('tenancy.domain.wildcard_domain', '*.collaborinbox.test');
        $baseHost = Str::after($wildcardDomain, '*.');
        $domain = 'test-tenant.' . $baseHost;
        
        $tenant->domains()->create(['domain' => $domain]);
        
        $resolver = new TenantResolver();

        // Act
        $foundTenant = $resolver->findTenant('test-tenant');

        // Assert
        $this->assertNotNull($foundTenant);
        $this->assertEquals($tenant->id, $foundTenant->id);
    }

    /** @test */
    public function it_returns_null_when_tenant_not_found()
    {
        // Arrange
        $resolver = new TenantResolver();

        // Act
        $foundTenant = $resolver->findTenant('non-existent');

        // Assert
        $this->assertNull($foundTenant);
    }
} 