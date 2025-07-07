# Notification Sounds

This directory contains sound files used by the AgentNotificationSystem for audio alerts.

## Required Files

- `notification.mp3` - Default notification sound (used for general notifications)

## Recommended Additional Sounds

You may want to add these additional sounds for different notification types:
- `message.mp3` - New message notification sound
- `assignment.mp3` - Thread assignment notification sound
- `mention.mp3` - Mention notification sound (higher priority)
- `warning.mp3` - Warning notification sound

## Sound File Requirements

- Keep sound files small (< 100KB) to reduce loading time
- Use MP3 format for best browser compatibility
- Short duration (1-3 seconds) recommended
- Ensure sounds are distinct and recognizable

## Customizing Sounds

To use custom sounds, specify the sound URL when initializing the AgentNotificationSystem:

```javascript
AgentNotificationSystem.init({
  soundUrl: '/custom/path/to/sound.mp3'
});
```

You can also dynamically change sounds by modifying the `notificationSound` property:

```javascript
AgentNotificationSystem.notificationSound.src = '/custom/path/to/new-sound.mp3';
AgentNotificationSystem.notificationSound.load();
``` 