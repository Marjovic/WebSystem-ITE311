<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'message',
        'type',
        'reference_id',
        'reference_type',
        'is_read',
        'is_hidden'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'message' => 'required|string|max_length[255]',
        'type'    => 'permit_empty|in_list[info,success,warning,error,assignment,grade,enrollment]',
        'is_read' => 'permit_empty|in_list[0,1]',
        'is_hidden' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer'  => 'User ID must be an integer'
        ],
        'message' => [
            'required'   => 'Message is required',
            'string'     => 'Message must be a string',
            'max_length' => 'Message cannot exceed 255 characters'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get the count of unread notifications for a specific user
     */
    public function getUnreadCount($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->where('is_hidden', 0)
                    ->countAllResults();
    }

    /**
     * Get ALL notifications for a specific user
     */
    public function getNotificationsForUser($userId, $limit = null)
    {
        $builder = $this->where('user_id', $userId)
                       ->where('is_hidden', 0)
                       ->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->findAll();
    }

    /**
     * Get unread notifications only
     */
    public function getUnreadNotifications($userId, $limit = null)
    {
        $builder = $this->where('user_id', $userId)
                       ->where('is_read', 0)
                       ->where('is_hidden', 0)
                       ->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->findAll();
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead($notificationId)
    {
        return $this->update($notificationId, ['is_read' => 1]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->set('is_read', 1)
                    ->update();
    }

    /**
     * Hide a specific notification
     */
    public function hideNotification($notificationId)
    {
        return $this->update($notificationId, ['is_hidden' => 1]);
    }

    /**
     * Clear all notifications for a user (mark as hidden)
     */
    public function clearAllNotifications($userId)
    {
        return $this->where('user_id', $userId)
                    ->set('is_hidden', 1)
                    ->update();
    }

    /**
     * Create notification for user
     */
    public function createNotification($userId, $message, $type = 'info', $referenceId = null, $referenceType = null)
    {
        return $this->insert([
            'user_id'        => $userId,
            'message'        => $message,
            'type'           => $type,
            'reference_id'   => $referenceId,
            'reference_type' => $referenceType,
            'is_read'        => 0,
            'is_hidden'      => 0
        ]);
    }

    /**
     * Create notification for multiple users
     */
    public function createBulkNotifications($userIds, $message, $type = 'info', $referenceId = null, $referenceType = null)
    {
        $notifications = [];
        
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id'        => $userId,
                'message'        => $message,
                'type'           => $type,
                'reference_id'   => $referenceId,
                'reference_type' => $referenceType,
                'is_read'        => 0,
                'is_hidden'      => 0,
                'created_at'     => date('Y-m-d H:i:s')
            ];
        }
        
        return $this->insertBatch($notifications);
    }

    /**
     * Get notifications by type
     */
    public function getNotificationsByType($userId, $type)
    {
        return $this->where('user_id', $userId)
                    ->where('type', $type)
                    ->where('is_hidden', 0)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Delete old notifications (cleanup)
     */
    public function deleteOldNotifications($days = 30)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->where('created_at <', $date)
                    ->where('is_read', 1)
                    ->delete();
    }

    /**
     * Get notification statistics for a user
     */
    public function getUserStats($userId)
    {
        return [
            'total'      => $this->where('user_id', $userId)->where('is_hidden', 0)->countAllResults(),
            'unread'     => $this->getUnreadCount($userId),
            'read'       => $this->where('user_id', $userId)->where('is_read', 1)->where('is_hidden', 0)->countAllResults(),
            'by_type'    => $this->getTypeBreakdown($userId)
        ];
    }

    /**
     * Get breakdown by notification type
     */
    private function getTypeBreakdown($userId)
    {
        return $this->select('type, COUNT(*) as count')
                    ->where('user_id', $userId)
                    ->where('is_hidden', 0)
                    ->groupBy('type')
                    ->findAll();
    }
}