<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Notifications extends BaseController
{
    protected $notificationModel;
    
    public function __construct()
    {
        $this->notificationModel = new \App\Models\NotificationModel();
    }
    
    /**
     * Get notifications - Returns JSON response with unread count and notification list
     * Called via AJAX to fetch current user's notifications
     * 
     * @return ResponseInterface JSON response containing unread count and notifications
     */
    public function get()
    {
        // Check if user is logged in
        if ($this->session->get('isLoggedIn') !== true) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - Please login'
            ])->setStatusCode(401);
        }
        
        // Get current user ID from session
        $userID = $this->session->get('userID');
        
        // Get unread notification count
        $unreadCount = $this->notificationModel->getUnreadCount($userID);
        
        // Get latest notifications (default limit is 5)
        $notifications = $this->notificationModel->getNotificationsForUser($userID, 5);
        
        // Format notifications for display
        foreach ($notifications as &$notification) {
            $notification['formatted_date'] = date('M j, Y g:i A', strtotime($notification['created_at']));
            $notification['is_unread'] = ($notification['is_read'] == 0);
        }
        
        // Return JSON response
        return $this->response->setJSON([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Mark notification as read - Accepts notification ID via POST
     * Updates the notification's is_read status to 1
     * 
     * @param int $id The notification ID to mark as read
     * @return ResponseInterface JSON response indicating success or failure
     */
    public function mark_as_read($id = null)
    {
        // Check if user is logged in
        if ($this->session->get('isLoggedIn') !== true) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - Please login'
            ])->setStatusCode(401);
        }
        
        // Validate notification ID
        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid notification ID'
            ])->setStatusCode(400);
        }
          // Get current user ID from session
        $userID = $this->session->get('userID');
        
        // Verify the notification belongs to the current user before marking as read
        $notification = $this->notificationModel->where('id', $id)->first();
        
        if (!$notification) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Notification not found'
            ])->setStatusCode(404);
        }
        
        // Security check: Ensure the notification belongs to the logged-in user
        if ($notification['user_id'] != $userID) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized - This notification does not belong to you'
            ])->setStatusCode(403);
        }
        
        // Mark notification as read
        $result = $this->notificationModel->markAsRead($id);
        
        if ($result) {
            // Get updated unread count
            $unreadCount = $this->notificationModel->getUnreadCount($userID);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Notification marked as read',
                'unread_count' => $unreadCount
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ])->setStatusCode(500);
        }
    }
}
