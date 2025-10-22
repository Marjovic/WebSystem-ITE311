/**
 * Notification System - jQuery Implementation
 * 
 * This file handles all notification-related functionality including:
 * - Loading notifications via AJAX
 * - Updating badge counts
 * - Populating notification dropdown
 * - Marking notifications as read
 * 
 * Dependencies: jQuery 3.7.1+, Bootstrap 5.3.3+
 */

$(document).ready(function() {
    // Load notifications on page load
    loadNotifications();
    
    // Reload notifications when dropdown is opened
    $('#notificationsDropdown').on('click', function() {
        loadNotifications();
    });
    
    // Optional: Auto-refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
});

/**
 * Load notifications using jQuery AJAX
 * Uses $.get() to call /notifications endpoint
 */
function loadNotifications() {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';
    
    $.get(baseUrl + 'notifications', function(data) {
        if (data.success) {
            // Update badge count
            updateNotificationBadge(data.unread_count);
            
            // Populate dropdown menu with notifications
            populateNotifications(data.notifications);
        }
    }).fail(function(xhr, status, error) {
        console.error('Failed to load notifications:', error);
        $('#notificationsList').html(`
            <li class="text-center py-3">
                <i class="fas fa-exclamation-triangle text-danger fs-3"></i>
                <p class="mb-0 small text-danger mt-2">Failed to load notifications</p>
            </li>
        `);
    });
}

/**
 * Update notification badge count
 * If count is 0, hide badge; otherwise show it
 */
function updateNotificationBadge(count) {
    const badge = $('#notificationBadge');
    
    if (count > 0) {
        // Show badge with count
        badge.text(count > 99 ? '99+' : count);
        badge.show();
    } else {
        // Hide badge when count is 0
        badge.hide();
    }
}

/**
 * Populate dropdown menu with notification list
 * Uses Bootstrap alert classes for styling
 */
function populateNotifications(notifications) {
    const list = $('#notificationsList');
    list.empty();
    
    if (notifications.length === 0) {
        // No notifications - show empty state
        list.html(`
            <li class="px-3 py-4 text-center">
                <i class="fas fa-bell-slash text-muted fs-2 mb-2"></i>
                <p class="mb-0 text-muted">No notifications</p>
                <small class="text-muted">You're all caught up!</small>
            </li>
        `);
        return;
    }
    
    // Add each notification to the list
    notifications.forEach(function(notification, index) {
        const alertClass = notification.is_unread ? 'alert-info' : 'alert-light';
        const boldClass = notification.is_unread ? 'fw-bold' : '';
        const iconColor = notification.is_unread ? 'text-primary' : 'text-muted';
        
        const notificationHtml = `
            <li id="notification-${notification.id}" class="border-bottom">
                <div class="alert ${alertClass} mb-0 rounded-0 border-0" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle ${iconColor} me-2 mt-1"></i>
                        <div class="flex-grow-1">
                            <p class="mb-1 small ${boldClass}">
                                ${escapeHtml(notification.message)}
                            </p>
                            <small class="text-muted d-block">
                                <i class="fas fa-clock me-1"></i>
                                ${notification.formatted_date}
                            </small>
                            ${notification.is_unread ? `
                                <button class="btn btn-sm btn-primary mt-2 mark-read-btn" 
                                        data-id="${notification.id}"
                                        onclick="markAsRead(${notification.id})">
                                    <i class="fas fa-check me-1"></i>
                                    Mark as Read
                                </button>
                            ` : `
                                <span class="badge bg-success mt-2">
                                    <i class="fas fa-check-circle"></i> Read
                                </span>
                            `}
                        </div>
                    </div>
                </div>
            </li>
        `;
        
        list.append(notificationHtml);
    });
}

/**
 * Mark notification as read
 * Uses $.post() to call /notifications/mark_read/{id} endpoint
 * Upon success, removes notification from list and updates badge
 */
function markAsRead(notificationId) {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const csrfHash = $('meta[name="csrf-hash"]').attr('content');
    
    // Disable the button to prevent double-clicks
    $(`button[data-id="${notificationId}"]`).prop('disabled', true).html(`
        <span class="spinner-border spinner-border-sm me-1"></span>
        Marking...
    `);
    
    $.post(
        baseUrl + 'notifications/mark_read/' + notificationId,
        JSON.stringify({ [csrfToken]: csrfHash }),
        function(data) {
            if (data.success) {
                // Remove notification from list with fade effect
                $(`#notification-${notificationId}`).fadeOut(300, function() {
                    $(this).remove();
                    
                    // Update badge count
                    updateNotificationBadge(data.unread_count);
                    
                    // If no notifications left, show empty state
                    if ($('#notificationsList li').length === 0) {
                        $('#notificationsList').html(`
                            <li class="px-3 py-4 text-center">
                                <i class="fas fa-bell-slash text-muted fs-2 mb-2"></i>
                                <p class="mb-0 text-muted">No notifications</p>
                                <small class="text-muted">You're all caught up!</small>
                            </li>
                        `);
                    }
                });
                
                // Show success toast (optional)
                showToast('Success', 'Notification marked as read', 'success');
            } else {
                // Show error message
                alert('Failed to mark notification as read: ' + data.message);
                // Re-enable button
                $(`button[data-id="${notificationId}"]`).prop('disabled', false).html(`
                    <i class="fas fa-check me-1"></i> Mark as Read
                `);
            }
        },
        'json'
    ).fail(function(xhr, status, error) {
        console.error('Error marking notification as read:', error);
        alert('An error occurred while marking the notification as read');
        // Re-enable button
        $(`button[data-id="${notificationId}"]`).prop('disabled', false).html(`
            <i class="fas fa-check me-1"></i> Mark as Read
        `);
    });
}

/**
 * Helper function to escape HTML and prevent XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Optional: Show toast notification
 */
function showToast(title, message, type = 'info') {
    // Simple console log for now - can be enhanced with Bootstrap toasts
    console.log(`[${type.toUpperCase()}] ${title}: ${message}`);
}
