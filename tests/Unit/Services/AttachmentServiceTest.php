<?php

namespace Tests\Unit\Services;

use App\Services\AttachmentService;
use App\Services\TenantContext;
use PHPUnit\Framework\TestCase;
use Mockery;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Attachment as ImapAttachment;

class AttachmentServiceTest extends TestCase
{
    protected $attachmentService;
    protected $mockStorage;
    protected $mockTenantContext;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Mock dependencies
        $this->mockStorage = Mockery::mock('alias:Illuminate\Support\Facades\Storage');
        $this->mockTenantContext = Mockery::mock(TenantContext::class);
        
        // Mock app container to return our mocked TenantContext
        app()->instance(TenantContext::class, $this->mockTenantContext);
        
        // Setup basic tenant context behavior
        $this->mockTenantContext->shouldReceive('hasTenant')->andReturn(true);
        $this->mockTenantContext->shouldReceive('getTenantId')->andReturn(1);
        
        // Mock Log facade
        Log::shouldReceive('error')->withAnyArgs()->andReturn(null);
        Log::shouldReceive('warning')->withAnyArgs()->andReturn(null);
        
        $this->attachmentService = new AttachmentService();
    }
    
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Test processing attachments from an email
     */
    public function testProcessAttachments()
    {
        // Create mock Message with attachments
        $message = Mockery::mock(Message::class);
        $message->shouldReceive('hasAttachments')->andReturn(true);
        
        // Create mock attachments
        $attachment1 = $this->createMockAttachment('test.pdf', 'application/pdf', 10240, false);
        $attachment2 = $this->createMockAttachment('image.jpg', 'image/jpeg', 5120, true);
        
        // Set up message to return our mock attachments
        $message->shouldReceive('getAttachments')->andReturn([$attachment1, $attachment2]);
        
        // Mock storage to accept attachments
        $this->mockStorage->shouldReceive('put')->andReturn(true);
        
        // Process attachments
        $result = $this->attachmentService->processAttachments($message, 'email123');
        
        // Check that we get the expected attachment metadata
        $this->assertCount(2, $result);
        $this->assertEquals('test.pdf', $result[0]['original_filename']);
        $this->assertEquals('application/pdf', $result[0]['content_type']);
        $this->assertEquals(10240, $result[0]['size']);
        $this->assertEquals('image.jpg', $result[1]['original_filename']);
        $this->assertEquals('image/jpeg', $result[1]['content_type']);
        $this->assertEquals(5120, $result[1]['size']);
        $this->assertTrue($result[1]['is_inline']);
    }
    
    /**
     * Test skipping oversized attachments
     */
    public function testSkipOversizedAttachments()
    {
        // Create mock Message with attachments
        $message = Mockery::mock(Message::class);
        $message->shouldReceive('hasAttachments')->andReturn(true);
        
        // Create a mock attachment that exceeds the size limit (>10MB)
        $largeAttachment = $this->createMockAttachment('large.zip', 'application/zip', 15 * 1024 * 1024, false);
        
        // Set up message to return our mock attachment
        $message->shouldReceive('getAttachments')->andReturn([$largeAttachment]);
        
        // Process attachments
        $result = $this->attachmentService->processAttachments($message, 'email123');
        
        // Check that no attachments were processed due to size limit
        $this->assertCount(0, $result);
    }
    
    /**
     * Test handling messages without attachments
     */
    public function testNoAttachments()
    {
        // Create mock Message without attachments
        $message = Mockery::mock(Message::class);
        $message->shouldReceive('hasAttachments')->andReturn(false);
        
        // Process attachments
        $result = $this->attachmentService->processAttachments($message, 'email123');
        
        // Check that we get an empty result
        $this->assertCount(0, $result);
    }
    
    /**
     * Test tenant isolation in attachment storage paths
     */
    public function testTenantIsolation()
    {
        // Create mock Message with attachment
        $message = Mockery::mock(Message::class);
        $message->shouldReceive('hasAttachments')->andReturn(true);
        
        // Create mock attachment
        $attachment = $this->createMockAttachment('test.pdf', 'application/pdf', 10240, false);
        
        // Set up message to return our mock attachment
        $message->shouldReceive('getAttachments')->andReturn([$attachment]);
        
        // Set expectation that storage path includes tenant ID
        $this->mockStorage->shouldReceive('put')
            ->withArgs(function ($path, $content) {
                // The path should contain the tenant ID (1)
                return strpos($path, 'tenants/1/attachments') !== false;
            })
            ->andReturn(true);
        
        // Process attachment
        $this->attachmentService->processAttachments($message, 'email123');
    }
    
    /**
     * Test preview generation detection
     */
    public function testCanGeneratePreview()
    {
        // Test previewable types
        $this->assertTrue($this->attachmentService->canGeneratePreview('image/jpeg'));
        $this->assertTrue($this->attachmentService->canGeneratePreview('application/pdf'));
        $this->assertTrue($this->attachmentService->canGeneratePreview('text/plain'));
        
        // Test non-previewable types
        $this->assertFalse($this->attachmentService->canGeneratePreview('application/zip'));
        $this->assertFalse($this->attachmentService->canGeneratePreview('application/octet-stream'));
    }
    
    /**
     * Helper method to create mock attachments
     */
    protected function createMockAttachment(string $filename, string $contentType, int $size, bool $isInline): ImapAttachment
    {
        $attachment = Mockery::mock(ImapAttachment::class);
        $attachment->shouldReceive('getName')->andReturn($filename);
        $attachment->shouldReceive('getContentType')->andReturn($contentType);
        $attachment->shouldReceive('getSize')->andReturn($size);
        
        if ($isInline) {
            $attachment->shouldReceive('getContentId')->andReturn('content-' . $filename);
            $attachment->shouldReceive('getContentDisposition')->andReturn('inline');
        } else {
            $attachment->shouldReceive('getContentId')->andReturn(null);
            $attachment->shouldReceive('getContentDisposition')->andReturn('attachment');
        }
        
        $attachment->shouldReceive('getContent')->andReturn('mock content');
        
        return $attachment;
    }
} 