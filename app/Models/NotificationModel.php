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
    protected $allowedFields    = ['user_id', 'message', 'is_read', 'created_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'user_id' => 'required|integer',
        'message' => 'required|string|max_length[255]',
        'is_read' => 'in_list[0,1]'
    ];
    protected $validationMessages   = [
        'user_id' => [
            'required' => 'User ID is required.',
            'integer'  => 'User ID must be an integer.'
        ],
        'message' => [
            'required'   => 'Message is required.',
            'string'     => 'Message must be a string.',
            'max_length' => 'Message cannot exceed 255 characters.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get the count of unread notifications for a specific user
     * 
     * @param int $userId The ID of the user
     * @return int The count of unread notifications
     */
    public function getUnreadCount($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    /**
     * Get the latest notifications for a specific user
     * 
     * @param int $userId The ID of the user
     * @param int $limit The maximum number of notifications to retrieve (default: 5)
     * @return array An array of notifications
     */
    public function getNotificationsForUser($userId, $limit = 5)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }   
    
    /**
     * Mark a specific notification as read
     * 
     * @param int $notificationId The ID of the notification to mark as read
     * @return bool True if the update was successful, false otherwise
     */
    public function markAsRead($notificationId)
    {
        return $this->update($notificationId, ['is_read' => 1]);
    }
}
