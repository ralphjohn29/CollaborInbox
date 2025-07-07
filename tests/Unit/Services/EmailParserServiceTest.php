<?php

namespace Tests\Unit\Services;

use App\Services\EmailParserService;
use App\Services\AttachmentService;
use PHPUnit\Framework\TestCase;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Header;
use Webklex\PHPIMAP\Attribute;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\IMAP;
use Illuminate\Support\Facades\Log;
use Mockery;

class EmailParserServiceTest extends TestCase
{
    protected $parserService;
    protected $mockAttachmentService;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Mock AttachmentService
        $this->mockAttachmentService = Mockery::mock(AttachmentService::class);
        
        // Set up default behavior for the attachment service
        $this->mockAttachmentService->shouldReceive('processAttachments')
            ->andReturn([
                [
                    'original_filename' => 'test-attachment.pdf',
                    'stored_filename' => 'test-attachment_12345678.pdf',
                    'path' => 'tenants/1/attachments/test-email-id/test-attachment_12345678.pdf',
                    'content_id' => null,
                    'content_type' => 'application/pdf',
                    'size' => 12345,
                    'is_inline' => false,
                    'can_preview' => true
                ]
            ]);
        
        $this->parserService = new EmailParserService($this->mockAttachmentService);
        
        // Mock Log facade
        Log::shouldReceive('error')->withAnyArgs()->andReturn(null);
    }
    
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Test basic email parsing functionality
     */
    public function testParseMessage()
    {
        // Create a mock Message object
        $message = $this->createMockMessage([
            'message_id' => '<test123@example.com>',
            'subject' => 'Test Subject',
            'from' => [['mail' => 'sender@example.com', 'personal' => 'Sender Name', 'full' => 'Sender Name <sender@example.com>']],
            'to' => [['mail' => 'recipient@example.com', 'personal' => 'Recipient Name', 'full' => 'Recipient Name <recipient@example.com>']],
            'date' => '2023-06-15 10:00:00',
            'html_body' => '<html><body><p>This is a test email</p></body></html>',
            'text_body' => 'This is a test email',
            'has_attachments' => false,
            'is_flagged' => false,
            'is_answered' => false,
            'is_deleted' => false,
            'is_draft' => false,
            'is_seen' => true
        ]);
        
        // Parse the message
        $parsedEmail = $this->parserService->parseMessage($message);
        
        // Assert basic properties are correctly parsed
        $this->assertEquals('<test123@example.com>', $parsedEmail['message_id']);
        $this->assertEquals('Test Subject', $parsedEmail['subject']);
        $this->assertEquals('sender@example.com', $parsedEmail['from'][0]['email']);
        $this->assertEquals('Sender Name', $parsedEmail['from'][0]['name']);
        $this->assertEquals('recipient@example.com', $parsedEmail['to'][0]['email']);
        $this->assertEquals('Recipient Name', $parsedEmail['to'][0]['name']);
        $this->assertFalse($parsedEmail['has_attachments']);
        $this->assertTrue($parsedEmail['is_seen']);
        $this->assertFalse($parsedEmail['is_flagged']);
        $this->assertEquals('normal', $parsedEmail['importance']);
        
        // Assert bodies are correctly parsed
        $this->assertEquals('<html><body><p>This is a test email</p></body></html>', $parsedEmail['body_html']);
        $this->assertEquals('This is a test email', $parsedEmail['body_plain']);
        
        // Assert attachments array is empty
        $this->assertEmpty($parsedEmail['attachments']);
    }
    
    /**
     * Test parsing of email with attachments
     */
    public function testParseMessageWithAttachments()
    {
        // Create a mock Message with attachments
        $message = $this->createMockMessage([
            'message_id' => '<test-with-attachments@example.com>',
            'subject' => 'Email with Attachments',
            'from' => [['mail' => 'sender@example.com', 'personal' => 'Sender', 'full' => 'Sender <sender@example.com>']],
            'to' => [['mail' => 'recipient@example.com', 'personal' => 'Recipient', 'full' => 'Recipient <recipient@example.com>']],
            'date' => '2023-06-15 11:30:00',
            'text_body' => 'This email has attachments',
            'has_attachments' => true
        ]);
        
        // Configure the mock attachment service for this test
        $this->mockAttachmentService->shouldReceive('processAttachments')
            ->once()
            ->with($message, Mockery::any())
            ->andReturn([
                [
                    'original_filename' => 'document.pdf',
                    'stored_filename' => 'document_abcd1234.pdf',
                    'path' => 'tenants/1/attachments/test-email-id/document_abcd1234.pdf',
                    'content_id' => null,
                    'content_type' => 'application/pdf',
                    'size' => 54321,
                    'is_inline' => false,
                    'can_preview' => true
                ],
                [
                    'original_filename' => 'image.jpg',
                    'stored_filename' => 'image_efgh5678.jpg',
                    'path' => 'tenants/1/attachments/test-email-id/image_efgh5678.jpg',
                    'content_id' => 'cid:image.jpg',
                    'content_type' => 'image/jpeg',
                    'size' => 12345,
                    'is_inline' => true,
                    'can_preview' => true
                ]
            ]);
        
        // Parse the message
        $parsedEmail = $this->parserService->parseMessage($message);
        
        // Assert basic properties
        $this->assertEquals('<test-with-attachments@example.com>', $parsedEmail['message_id']);
        $this->assertEquals('Email with Attachments', $parsedEmail['subject']);
        $this->assertTrue($parsedEmail['has_attachments']);
        
        // Assert attachments were processed
        $this->assertCount(2, $parsedEmail['attachments']);
        $this->assertEquals('document.pdf', $parsedEmail['attachments'][0]['original_filename']);
        $this->assertEquals('application/pdf', $parsedEmail['attachments'][0]['content_type']);
        $this->assertEquals('image.jpg', $parsedEmail['attachments'][1]['original_filename']);
        $this->assertTrue($parsedEmail['attachments'][1]['is_inline']);
    }
    
    /**
     * Test parsing of email with references and in-reply-to headers
     */
    public function testParseMessageWithThreadInfo()
    {
        // Create a mock Message with thread information
        $message = $this->createMockMessage([
            'message_id' => '<reply123@example.com>',
            'subject' => 'Re: Original Subject',
            'from' => [['mail' => 'sender@example.com', 'personal' => 'Sender', 'full' => 'Sender <sender@example.com>']],
            'to' => [['mail' => 'recipient@example.com', 'personal' => 'Recipient', 'full' => 'Recipient <recipient@example.com>']],
            'date' => '2023-06-15 10:15:00',
            'references' => '<original123@example.com> <intermediate@example.com>',
            'in_reply_to' => '<original123@example.com>',
            'text_body' => 'This is a reply',
            'has_attachments' => false,
            'is_seen' => false
        ]);
        
        // Parse the message
        $parsedEmail = $this->parserService->parseMessage($message);
        
        // Assert threading information is correctly parsed
        $this->assertEquals('<reply123@example.com>', $parsedEmail['message_id']);
        $this->assertEquals('<original123@example.com>', $parsedEmail['in_reply_to']);
        $this->assertCount(2, $parsedEmail['references']);
        $this->assertEquals('<original123@example.com>', $parsedEmail['references'][0]);
        $this->assertEquals('<intermediate@example.com>', $parsedEmail['references'][1]);
    }
    
    /**
     * Test error handling when parsing a problematic email
     */
    public function testParseMessageWithErrors()
    {
        // Create a mock Message that will cause errors
        $message = Mockery::mock(Message::class);
        $message->shouldReceive('getMessageId')->andReturn('<error@example.com>');
        $message->shouldReceive('getSubject')->andReturn('Error Test');
        $message->shouldReceive('getDate')->andThrow(new \Exception('Date parsing error'));
        $message->shouldReceive('getFrom')->andReturn([]);
        $message->shouldReceive('hasAttachments')->andReturn(false);
        
        // Parse the message
        $parsedEmail = $this->parserService->parseMessage($message);
        
        // Assert we get back minimal data with error information
        $this->assertEquals('<error@example.com>', $parsedEmail['message_id']);
        $this->assertEquals('Error Test', $parsedEmail['subject']);
        $this->assertEmpty($parsedEmail['from']);
        $this->assertArrayHasKey('parse_error', $parsedEmail);
        $this->assertEquals('Date parsing error', $parsedEmail['parse_error']);
        $this->assertEmpty($parsedEmail['attachments']);
    }
    
    /**
     * Test the creation of a standardized DTO
     */
    public function testCreateEmailDTO()
    {
        // Create a mock Message
        $message = $this->createMockMessage([
            'message_id' => '<dto-test@example.com>',
            'subject' => 'DTO Test',
            'from' => [['mail' => 'sender@example.com', 'personal' => 'Sender', 'full' => 'Sender <sender@example.com>']],
            'to' => [['mail' => 'recipient@example.com', 'personal' => 'Recipient', 'full' => 'Recipient <recipient@example.com>']],
            'cc' => [['mail' => 'cc@example.com', 'personal' => 'CC', 'full' => 'CC <cc@example.com>']],
            'date' => '2023-06-15 11:00:00',
            'html_body' => '<p>DTO test</p>',
            'text_body' => 'DTO test',
            'has_attachments' => true,
            'is_flagged' => true,
            'is_seen' => false,
            'importance' => 'high'
        ]);
        
        // Expect the attachment service to be called for this message
        $this->mockAttachmentService->shouldReceive('processAttachments')
            ->once()
            ->with($message, Mockery::any())
            ->andReturn([
                [
                    'original_filename' => 'attachment.txt',
                    'stored_filename' => 'attachment_12345678.txt',
                    'path' => 'tenants/1/attachments/test-email-id/attachment_12345678.txt',
                    'content_id' => null,
                    'content_type' => 'text/plain',
                    'size' => 1024,
                    'is_inline' => false,
                    'can_preview' => true
                ]
            ]);
        
        // Create DTO
        $dto = $this->parserService->createEmailDTO($message);
        
        // Assert DTO properties
        $this->assertEquals('<dto-test@example.com>', $dto->messageId);
        $this->assertEquals('DTO Test', $dto->subject);
        $this->assertEquals('sender@example.com', $dto->from[0]['email']);
        $this->assertEquals('Sender', $dto->from[0]['name']);
        $this->assertEquals('recipient@example.com', $dto->to[0]['email']);
        $this->assertEquals('cc@example.com', $dto->cc[0]['email']);
        $this->assertEquals('<p>DTO test</p>', $dto->bodyHtml);
        $this->assertEquals('DTO test', $dto->bodyPlain);
        $this->assertTrue($dto->hasAttachments);
        $this->assertTrue($dto->isFlagged);
        $this->assertFalse($dto->isRead);
        $this->assertEquals('high', $dto->importance);
        
        // Assert attachment data in DTO
        $this->assertCount(1, $dto->attachments);
        $this->assertEquals('attachment.txt', $dto->attachments[0]['original_filename']);
        $this->assertEquals('text/plain', $dto->attachments[0]['content_type']);
        $this->assertEquals(1024, $dto->attachments[0]['size']);
    }
    
    /**
     * Helper method to create mock Message objects
     */
    protected function createMockMessage(array $attributes): Message
    {
        $message = Mockery::mock(Message::class);
        
        // Set up basic getters
        $message->shouldReceive('getMessageId')->andReturn($attributes['message_id'] ?? null);
        $message->shouldReceive('getSubject')->andReturn($attributes['subject'] ?? null);
        
        // Set up date
        $dateObj = Mockery::mock();
        $dateObj->shouldReceive('toDateTime')->andReturn(new \DateTime($attributes['date'] ?? 'now'));
        $message->shouldReceive('getDate')->andReturn($dateObj);
        
        // Set up addresses
        $fromAddresses = $this->createAddressMocks($attributes['from'] ?? []);
        $toAddresses = $this->createAddressMocks($attributes['to'] ?? []);
        $ccAddresses = $this->createAddressMocks($attributes['cc'] ?? []);
        $bccAddresses = $this->createAddressMocks($attributes['bcc'] ?? []);
        
        $message->shouldReceive('getFrom')->andReturn($fromAddresses);
        $message->shouldReceive('getTo')->andReturn($toAddresses);
        $message->shouldReceive('getCc')->andReturn($ccAddresses);
        $message->shouldReceive('getBcc')->andReturn($bccAddresses);
        
        // Set up headers for threading
        $message->shouldReceive('getHeader')->with('references')->andReturn($attributes['references'] ?? null);
        $message->shouldReceive('getHeader')->with('in-reply-to')->andReturn($attributes['in_reply_to'] ?? null);
        $message->shouldReceive('getHeader')->with('importance')->andReturn($attributes['importance'] ?? null);
        $message->shouldReceive('getHeader')->with('x-priority')->andReturn($attributes['x_priority'] ?? null);
        
        // Set up body content
        $message->shouldReceive('getHTMLBody')->andReturn($attributes['html_body'] ?? null);
        $message->shouldReceive('getTextBody')->andReturn($attributes['text_body'] ?? null);
        $message->shouldReceive('getBody')->andReturn($attributes['body'] ?? ($attributes['text_body'] ?? ''));
        
        // Set up flags
        $message->shouldReceive('hasAttachments')->andReturn($attributes['has_attachments'] ?? false);
        $message->shouldReceive('getSize')->andReturn($attributes['size'] ?? 1024);
        $message->shouldReceive('isFlagged')->andReturn($attributes['is_flagged'] ?? false);
        $message->shouldReceive('isAnswered')->andReturn($attributes['is_answered'] ?? false);
        $message->shouldReceive('isDeleted')->andReturn($attributes['is_deleted'] ?? false);
        $message->shouldReceive('isDraft')->andReturn($attributes['is_draft'] ?? false);
        $message->shouldReceive('isSeen')->andReturn($attributes['is_seen'] ?? false);
        
        return $message;
    }
    
    /**
     * Helper to create mock Address objects
     */
    protected function createAddressMocks(array $addresses): array
    {
        $mocks = [];
        
        foreach ($addresses as $address) {
            $mock = new \stdClass();
            $mock->mail = $address['mail'];
            $mock->personal = $address['personal'] ?? null;
            $mock->full = $address['full'];
            $mocks[] = $mock;
        }
        
        return $mocks;
    }
} 