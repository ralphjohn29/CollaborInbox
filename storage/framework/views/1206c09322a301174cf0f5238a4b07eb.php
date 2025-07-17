<div id="emailDetailContent">
    <div class="email-detail-header" style="background: white; padding: 1.5rem; border-bottom: 1px solid #e5e7eb; border-radius: 8px 8px 0 0;">
        <div class="email-actions" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <button class="btn btn-outline" onclick="toggleStar(<?php echo e($email->id); ?>)" style="background: white; color: #6b7280; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                <i id="star-<?php echo e($email->id); ?>" class="fas fa-star star-icon <?php echo e($email->is_starred ? 'starred' : ''); ?>"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-outline dropdown-toggle" onclick="toggleDropdown('statusDropdown')" style="background: white; color: #6b7280; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-tag"></i> Status
                </button>
                <div class="dropdown-menu" id="statusDropdown" style="position: absolute; top: 100%; left: 0; background: white; border: 1px solid #d1d5db; border-radius: 6px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); min-width: 150px; z-index: 1000;">
                    <a href="#" class="dropdown-item" onclick="updateStatus(<?php echo e($email->id); ?>, 'unread')" style="display: block; padding: 0.5rem 1rem; text-decoration: none; color: #374151; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Mark as Unread</a>
                    <a href="#" class="dropdown-item" onclick="updateStatus(<?php echo e($email->id); ?>, 'archived')" style="display: block; padding: 0.5rem 1rem; text-decoration: none; color: #374151; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Archive</a>
                    <a href="#" class="dropdown-item" onclick="updateStatus(<?php echo e($email->id); ?>, 'spam')" style="display: block; padding: 0.5rem 1rem; text-decoration: none; color: #374151; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Mark as Spam</a>
                    <a href="#" class="dropdown-item" onclick="updateStatus(<?php echo e($email->id); ?>, 'trash')" style="display: block; padding: 0.5rem 1rem; text-decoration: none; color: #374151; font-size: 14px;">Move to Trash</a>
                </div>
            </div>
            <button class="btn btn-outline back-button" onclick="hideEmailDetail()" style="background: white; color: #6b7280; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>
        
        <div class="email-detail-subject" style="font-size: 1.5rem; font-weight: 600; color: #111827; margin-bottom: 1rem;"><?php echo e($email->subject); ?></div>
        
        <div class="email-detail-meta" style="background: #f9fafb; padding: 1.5rem; border-radius: 6px; margin-bottom: 1rem; font-size: 14px; color: #6b7280;">
            <div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: <?php echo e($email->getAvatarColor()); ?>; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1rem; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                    <?php echo e($email->getAvatarInitials()); ?>

                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 1rem; font-weight: 600; color: #111827; margin-bottom: 0.25rem;">
                        <?php echo e($email->from_name ?: $email->from_email); ?>

                    </div>
                    <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">
                        <?php echo e($email->from_email); ?>

                    </div>
                    <div style="color: #9ca3af; font-size: 0.8rem;">
                        <?php echo e($email->received_at?->format('F j, Y \\a\\t g:i A')); ?>

                    </div>
                </div>
            </div>
            <div style="padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                <div style="margin-bottom: 0.5rem;">
                    <strong style="color: #374151;">To:</strong> <?php echo e($email->to_email); ?>

                </div>
                <?php if($email->cc): ?>
                    <div style="margin-bottom: 0.5rem;">
                        <strong style="color: #374151;">Cc:</strong> 
                        <?php if(is_array($email->cc)): ?>
                            <?php echo e(implode(', ', array_column($email->cc, 'address'))); ?>

                        <?php else: ?>
                            <?php echo e($email->cc); ?>

                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 1rem; background: #f3f4f6; padding: 1rem; border-radius: 6px; border: 1px solid #e5e7eb;">
            <div style="flex: 1;">
                <label style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem; display: block; font-weight: 500;">Assigned To</label>
                <select id="assignUserSelect" class="filter-select" onchange="assignUser(<?php echo e($email->id); ?>)" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; background: white; font-size: 14px; color: #374151;">
                    <option value="">Unassigned</option>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>" <?php echo e($email->assigned_to == $user->id ? 'selected' : ''); ?>>
                            <?php echo e($user->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div style="flex: 1;">
                <label style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem; display: block; font-weight: 500;">Disposition</label>
                <select id="dispositionSelect" class="filter-select" onchange="setDisposition(<?php echo e($email->id); ?>)" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; background: white; font-size: 14px; color: #374151;">
                    <option value="">No Disposition</option>
                    <?php $__currentLoopData = $dispositions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disposition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($disposition->id); ?>" <?php echo e($email->disposition_id == $disposition->id ? 'selected' : ''); ?>>
                            <?php echo e($disposition->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
    </div>
    
    <div class="email-detail-content" style="background: white; padding: 1.5rem; border-radius: 0 0 8px 8px;">
        <div class="email-detail-body" style="font-size: 15px; line-height: 1.7; color: #374151; margin-bottom: 1.5rem;">
            <?php if($email->body_html): ?>
                <?php echo $email->body_html; ?>

            <?php else: ?>
                <?php echo nl2br(e($email->body_text)); ?>

            <?php endif; ?>
        </div>
        
        <?php if($email->attachments->count() > 0): ?>
            <div class="attachments-section">
                <h3 class="attachments-title">Attachments (<?php echo e($email->attachments->count()); ?>)</h3>
                <div class="attachments-list">
                    <?php $__currentLoopData = $email->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('inbox.attachment.download', $attachment->id)); ?>" class="attachment-item" target="_blank">
                            <div class="attachment-icon">
                                <?php if($attachment->isImage()): ?>
                                    <i class="fas fa-image"></i>
                                <?php elseif($attachment->isPdf()): ?>
                                    <i class="fas fa-file-pdf"></i>
                                <?php elseif($attachment->isDocument()): ?>
                                    <i class="fas fa-file-alt"></i>
                                <?php else: ?>
                                    <i class="fas fa-file"></i>
                                <?php endif; ?>
                            </div>
                            <div class="attachment-info">
                                <div class="attachment-name"><?php echo e($attachment->filename); ?></div>
                                <div class="attachment-size"><?php echo e($attachment->getSizeFormatted()); ?></div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if(count($threadEmails) > 0): ?>
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid hsl(var(--border));">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Thread History</h3>
                <?php $__currentLoopData = $threadEmails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $threadEmail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="padding: 1rem; border: 1px solid hsl(var(--border)); border-radius: calc(var(--radius) - 2px); margin-bottom: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <strong><?php echo e($threadEmail->from_name ?: $threadEmail->from_email); ?></strong>
                            <span style="font-size: 0.75rem; color: hsl(var(--muted-foreground));">
                                <?php echo e($threadEmail->received_at?->format('M j, g:i a')); ?>

                            </span>
                        </div>
                        <div style="color: hsl(var(--muted-foreground)); font-size: 0.875rem;">
                            <?php echo e($threadEmail->getPreviewText(200)); ?>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        
        <?php if($email->replies->count() > 0): ?>
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid hsl(var(--border));">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Replies</h3>
                <?php $__currentLoopData = $email->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="padding: 1rem; border: 1px solid hsl(var(--border)); border-radius: calc(var(--radius) - 2px); margin-bottom: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <strong><?php echo e($reply->user->name ?? 'Unknown User'); ?></strong>
                            <span style="font-size: 0.75rem; color: hsl(var(--muted-foreground));">
                                <?php echo e($reply->sent_at?->format('M j, g:i a')); ?>

                            </span>
                        </div>
                        <div>
                            <?php if($reply->body_html): ?>
                                <?php echo $reply->body_html; ?>

                            <?php else: ?>
                                <?php echo nl2br(e($reply->body_text)); ?>

                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="reply-section" style="padding: 1.5rem; border-top: 1px solid #e5e7eb; background: #ffffff; border-radius: 0 0 8px 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);">
        <div class="reply-actions" style="margin-bottom: 1rem; display: flex; gap: 0.75rem; align-items: center;">
            <button onclick="showReplyForm(<?php echo e($email->id); ?>)" class="btn btn-primary reply-btn" style="background: #2563eb; color: white; border: none; border-radius: 6px; padding: 0.625rem 1.25rem; font-size: 14px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.15s ease; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);">
                <i class="fas fa-reply"></i> Reply
            </button>
            <button onclick="toggleStar(<?php echo e($email->id); ?>)" id="star-btn-<?php echo e($email->id); ?>" class="btn btn-star" style="background: <?php echo e($email->is_starred ? '#fbbf24' : 'transparent'); ?>; border: 1px solid #fbbf24; color: <?php echo e($email->is_starred ? 'white' : '#fbbf24'); ?>; border-radius: 6px; padding: 0.625rem 1rem; font-size: 14px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.15s ease;">
                <i id="star-icon-<?php echo e($email->id); ?>" class="fas fa-star"></i> <?php echo e($email->is_starred ? 'Starred' : 'Star'); ?>

            </button>
        </div>
        <div id="reply-form-<?php echo e($email->id); ?>" class="reply-form" style="background: #f9fafb; border-radius: 8px; padding: 1.25rem; margin-top: 1rem; border: 1px solid #e5e7eb; display: none;">
            <form onsubmit="sendReply(event, <?php echo e($email->id); ?>)" style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <label for="reply-editor-<?php echo e($email->id); ?>" style="font-size: 14px; font-weight: 500; color: #374151;">Your Reply</label>
                    <div id="reply-editor-<?php echo e($email->id); ?>" class="reply-editor" style="background: white; border-radius: 6px; border: 1px solid #d1d5db; min-height: 200px;"></div>
                    <input type="hidden" id="reply-body-<?php echo e($email->id); ?>" name="reply_body" required>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <button type="submit" class="btn btn-primary" style="background: #2563eb; color: white; border: none; border-radius: 6px; padding: 0.625rem 1.25rem; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.15s ease; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                    <button type="button" onclick="hideReplyForm(<?php echo e($email->id); ?>)" class="btn btn-outline" style="background: white; color: #6b7280; border: 1px solid #d1d5db; border-radius: 6px; padding: 0.625rem 1.25rem; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.15s ease;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    dropdown.classList.toggle('show');
    
    // Close when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!e.target.closest('.dropdown')) {
            dropdown.classList.remove('show');
            document.removeEventListener('click', closeDropdown);
        }
    });
}

function hideEmailDetail() {
    document.querySelector('.email-detail').classList.remove('show');
}
</script>
<?php /**PATH D:\CollaborInbox\resources\views/inbox/show.blade.php ENDPATH**/ ?>