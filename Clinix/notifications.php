<?php
require_once 'include/db.php';

// Logging function for debugging
function logNotificationActivity($action, $data) {
    error_log("Notification {$action}: " . json_encode($data));
}

// Single unified function to create notifications
function createPatientNotification($patient_id, $message) {
    global $conn;
    try {
        // Log the attempt
        logNotificationActivity('attempt', [
            'patient_id' => $patient_id,
            'message' => $message
        ]);

        // Begin transaction
        $conn->beginTransaction();

        $query = "INSERT INTO notifications (patient_id, message, status, created_at) 
                 VALUES (:patient_id, :message, 'unread', NOW())";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([
            ':patient_id' => $patient_id,
            ':message' => $message
        ]);

        if ($result) {
            $conn->commit();
            logNotificationActivity('success', [
                'notification_id' => $conn->lastInsertId(),
                'patient_id' => $patient_id
            ]);
        } else {
            $conn->rollBack();
            logNotificationActivity('failed', [
                'patient_id' => $patient_id,
                'reason' => 'Execute returned false'
            ]);
        }

        return $result;
    } catch (PDOException $e) {
        $conn->rollBack();
        logNotificationActivity('error', [
            'error' => $e->getMessage(),
            'patient_id' => $patient_id
        ]);
        return false;
    }
}

// Function to get unread notification count
function getUnreadNotificationCount($user_id, $role) {
    global $conn;
    try {
        if ($role === 'patient') {
            $query = "SELECT COUNT(*) FROM notifications 
                     WHERE patient_id = :patient_id 
                     AND status = 'unread'";
            $stmt = $conn->prepare($query);
            $stmt->execute([':patient_id' => $user_id]);
            $count = $stmt->fetchColumn();
            
            logNotificationActivity('count', [
                'patient_id' => $user_id,
                'unread_count' => $count
            ]);
            
            return $count;
        }
        return 0;
    } catch (PDOException $e) {
        logNotificationActivity('error', [
            'action' => 'count',
            'error' => $e->getMessage()
        ]);
        return 0;
    }
}

// Function to get notifications
function getNotifications($user_id, $role) {
    global $conn;
    try {
        if ($role === 'patient') {
            $query = "SELECT 
                        notification_id,
                        message,
                        status,
                        created_at
                     FROM notifications 
                     WHERE patient_id = :patient_id 
                     ORDER BY created_at DESC 
                     LIMIT 10";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([':patient_id' => $user_id]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            logNotificationActivity('fetch', [
                'patient_id' => $user_id,
                'count' => count($notifications)
            ]);
            
            return $notifications;
        }
        return [];
    } catch (PDOException $e) {
        logNotificationActivity('error', [
            'action' => 'fetch',
            'error' => $e->getMessage()
        ]);
        return [];
    }
}

// Function to mark notification as read
function markNotificationAsRead($notification_id) {
    global $conn;
    try {
        $query = "UPDATE notifications 
                 SET status = 'read' 
                 WHERE notification_id = :notification_id";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([':notification_id' => $notification_id]);
        
        logNotificationActivity('mark_read', [
            'notification_id' => $notification_id,
            'success' => $result
        ]);
        
        return $result;
    } catch (PDOException $e) {
        logNotificationActivity('error', [
            'action' => 'mark_read',
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

// Function to notify about appointment status
function notifyPatientAboutAppointment($patient_id, $status, $appointment_date, $appointment_time) {
    try {
        $formatted_date = date('F d, Y', strtotime($appointment_date));
        $formatted_time = date('h:i A', strtotime($appointment_time));
        
        switch($status) {
            case 'Confirmed':
                $message = "Your appointment on {$formatted_date} at {$formatted_time} has been confirmed.";
                break;
            case 'Cancelled':
                $message = "Your appointment on {$formatted_date} at {$formatted_time} has been cancelled.";
                break;
            case 'Completed':
                $message = "Your appointment on {$formatted_date} at {$formatted_time} has been marked as completed.";
                break;
            default:
                $message = "Your appointment status for {$formatted_date} at {$formatted_time} has been updated to {$status}.";
        }
        
        return createPatientNotification($patient_id, $message);
    } catch (Exception $e) {
        logNotificationActivity('error', [
            'action' => 'create_appointment_notification',
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

// Function to delete old notifications
function deleteOldNotifications($days = 10) {
    global $conn;
    try {
        $query = "DELETE FROM notifications 
                 WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY) 
                 AND status = 'read'";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([':days' => $days]);
        
        logNotificationActivity('cleanup', [
            'days' => $days,
            'success' => $result
        ]);
        
        return $result;
    } catch (PDOException $e) {
        logNotificationActivity('error', [
            'action' => 'cleanup',
            'error' => $e->getMessage()
        ]);
        return false;
    }
}