<?php

namespace Tests\Unit\Events;

use App\Events\ThreadUpdatedEvent;
use App\Events\ThreadAssignedEvent;
use App\Events\NewMessageEvent;
use App\Models\Thread;
use App\Models\User;
use App\Models\Message;
use App\Services\BroadcastService;
use Illuminate\Broadcasting\PrivateChannel;
use Tests\TestCase;

class BroadcastEventsTest extends TestCase
{
    /**
     * Test ThreadUpdatedEvent channel configuration.
     */
    public function test_thread_updated_event_channels()
    {
        $thread = new Thread();
        $thread->id = 123;
        $thread->subject = 'Test Subject';
        $thread->status = 'open';
        $thread->assigned_to_id = null;
        $thread->last_activity_at = now();
        
        $tenantId = 456;
        
        $event = new ThreadUpdatedEvent($thread, $tenantId, 'updated');
        
        $channels = $event->broadcastOn();
        
        $this->assertCount(2, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertInstanceOf(PrivateChannel::class, $channels[1]);
        
        $this->assertEquals(
            'private-' . BroadcastService::getTenantChannelName($tenantId),
            $channels[0]->name
        );
        
        $this->assertEquals(
            'private-' . BroadcastService::getThreadChannelName($tenantId, $thread->id),
            $channels[1]->name
        );
    }
    
    /**
     * Test ThreadAssignedEvent channel configuration.
     */
    public function test_thread_assigned_event_channels()
    {
        $thread = new Thread();
        $thread->id = 123;
        
        $tenantId = 456;
        
        $newAssignee = new User();
        $newAssignee->id = 789;
        
        $event = new ThreadAssignedEvent($thread, $tenantId, null, $newAssignee);
        
        $channels = $event->broadcastOn();
        
        $this->assertCount(3, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertInstanceOf(PrivateChannel::class, $channels[1]);
        $this->assertInstanceOf(PrivateChannel::class, $channels[2]);
        
        $this->assertEquals(
            'private-' . BroadcastService::getUserChannelName($tenantId, $newAssignee->id),
            $channels[2]->name
        );
    }
    
    /**
     * Test NewMessageEvent channel configuration.
     */
    public function test_new_message_event_channels()
    {
        $thread = new Thread();
        $thread->id = 123;
        $thread->assigned_to_id = 789;
        
        $message = new Message();
        $message->id = 456;
        $message->thread_id = $thread->id;
        
        $tenantId = 456;
        
        $event = new NewMessageEvent($message, $thread, $tenantId);
        
        $channels = $event->broadcastOn();
        
        $this->assertCount(3, $channels);
        
        $this->assertEquals(
            'private-' . BroadcastService::getUserChannelName($tenantId, $thread->assigned_to_id),
            $channels[2]->name
        );
    }
    
    /**
     * Test the broadcast data format for ThreadUpdatedEvent.
     */
    public function test_thread_updated_event_data()
    {
        $thread = new Thread();
        $thread->id = 123;
        $thread->subject = 'Test Subject';
        $thread->status = 'open';
        $thread->assigned_to_id = null;
        $thread->last_activity_at = now();
        
        $tenantId = 456;
        
        $event = new ThreadUpdatedEvent($thread, $tenantId, 'updated', ['key' => 'value']);
        
        $data = $event->broadcastWith();
        
        $this->assertEquals(123, $data['id']);
        $this->assertEquals('Test Subject', $data['subject']);
        $this->assertEquals('open', $data['status']);
        $this->assertEquals('updated', $data['action']);
        $this->assertEquals(['key' => 'value'], $data['additional_data']);
        $this->assertEquals($tenantId, $data['tenant_id']);
    }
    
    /**
     * Test the event name for each event type.
     */
    public function test_event_names()
    {
        $thread = new Thread();
        $message = new Message();
        $tenantId = 456;
        
        $threadUpdatedEvent = new ThreadUpdatedEvent($thread, $tenantId, 'updated');
        $threadAssignedEvent = new ThreadAssignedEvent($thread, $tenantId, null, null);
        $newMessageEvent = new NewMessageEvent($message, $thread, $tenantId);
        
        $this->assertEquals('thread.updated', $threadUpdatedEvent->broadcastAs());
        $this->assertEquals('thread.assigned', $threadAssignedEvent->broadcastAs());
        $this->assertEquals('message.new', $newMessageEvent->broadcastAs());
    }
} 