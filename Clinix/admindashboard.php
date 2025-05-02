<?php
require_once 'session.php';
require_once 'include/db.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    try {
        switch ($_GET['action']) {
            case 'get_appointment':
                $stmt = $conn->prepare("
                    SELECT a.*, p.name as patient_name, d.name as doctor_name 
                    FROM appointments a 
                    JOIN patients p ON a.patient_id = p.patient_id 
                    JOIN doctors d ON a.doctor_id = d.doctor_id 
                    WHERE a.appointment_id = ?
                ");
                $stmt->execute([$_GET['id']]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
                exit;
                case 'get_admin':
                    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'admin'");
                    $stmt->execute([$_GET['id']]);
                    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
                    exit;
            case 'get_doctor':
                $stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
                exit;

            case 'get_patient':
                $stmt = $conn->prepare("SELECT * FROM patients WHERE patient_id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
                exit;
        }
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Appointment Actions
        if (isset($_POST['update_appointment'])) {
            $appointment_id = $_POST['appointment_id'];
            $status = $_POST['status'];
            $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
            $stmt->execute([$status, $appointment_id]);
            $_SESSION['success'] = "Appointment updated successfully";
        }

        // Doctor Actions
        if (isset($_POST['add_doctor'])) {
            $name = $_POST['name'];
            $contactinfo = $_POST['contactinfo'];
            $specialization = $_POST['specialization'];
            
            // First create user account
            $stmt = $conn->prepare("INSERT INTO users (username, password, name, contactinfo, role) VALUES (?, ?, ?, ?, 'doctor')");
            $username = strtolower(str_replace(' ', '', $name));
            $password = password_hash('defaultpass123', PASSWORD_DEFAULT);
            $stmt->execute([$username, $password, $name, $contactinfo]);
            
            $user_id = $conn->lastInsertId();
            
            // Then create doctor record
            $stmt = $conn->prepare("INSERT INTO doctors (user_id, name, contactinfo, specialization) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $contactinfo, $specialization]);
            $_SESSION['success'] = "Doctor added successfully";
        }
        // Admin Actions
if (isset($_POST['add_admin'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $contactinfo = $_POST['contactinfo'];
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, name, contactinfo, role) VALUES (?, ?, ?, ?, 'admin')");
    $stmt->execute([$username, $password, $name, $contactinfo]);
    $_SESSION['success'] = "Administrator added successfully";
}

if (isset($_POST['edit_admin'])) {
    $admin_id = $_POST['admin_id'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $contactinfo = $_POST['contactinfo'];
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, name = ?, contactinfo = ? WHERE user_id = ? AND role = 'admin'");
        $stmt->execute([$username, $password, $name, $contactinfo, $admin_id]);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, name = ?, contactinfo = ? WHERE user_id = ? AND role = 'admin'");
        $stmt->execute([$username, $name, $contactinfo, $admin_id]);
    }
    $_SESSION['success'] = "Administrator updated successfully";
}

// Delete Admin
if (isset($_POST['delete_admin'])) {
    try {
        $admin_id = $_POST['admin_id'];
        
        // Start transaction
        $conn->beginTransaction();
        
        // First delete from doctors table if exists
        $stmt = $conn->prepare("DELETE FROM doctors WHERE user_id = ?");
        $stmt->execute([$admin_id]);
        
        // Then delete from patients table if exists
        $stmt = $conn->prepare("DELETE FROM patients WHERE user_id = ?");
        $stmt->execute([$admin_id]);
        
        // Finally delete from users table
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'admin'");
        $stmt->execute([$admin_id]);
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Administrator deleted successfully";
    } catch(PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $_SESSION['error'] = "Error deleting administrator: " . $e->getMessage();
    }
}
        // Edit Doctor
        if (isset($_POST['edit_doctor'])) {
            $doctor_id = $_POST['doctor_id'];
            $name = $_POST['name'];
            $contactinfo = $_POST['contactinfo'];
            $specialization = $_POST['specialization'];
            
            $stmt = $conn->prepare("UPDATE doctors SET name = ?, contactinfo = ?, specialization = ? WHERE doctor_id = ?");
            $stmt->execute([$name, $contactinfo, $specialization, $doctor_id]);
            
            $_SESSION['success'] = "Doctor updated successfully";
        }

        // Delete Doctor
        if (isset($_POST['delete_doctor'])) {
            $doctor_id = $_POST['doctor_id'];
            $stmt = $conn->prepare("DELETE FROM doctors WHERE doctor_id = ?");
            $stmt->execute([$doctor_id]);
            $_SESSION['success'] = "Doctor deleted successfully";
        }

        // Patient Actions
        if (isset($_POST['add_patient'])) {
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $address = $_POST['address'];
            $location = $_POST['location'];
            
            // First create user account
            $stmt = $conn->prepare("INSERT INTO users (username, password, name, contactinfo, role) VALUES (?, ?, ?, ?, 'patient')");
            $username = strtolower(str_replace(' ', '', $name));
            $password = password_hash('defaultpass123', PASSWORD_DEFAULT);
            $stmt->execute([$username, $password, $name, $phone]);
            
            $user_id = $conn->lastInsertId();
            
            // Then create patient record
            $stmt = $conn->prepare("INSERT INTO patients (user_id, name, phone, address, location) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $phone, $address, $location]);
            $_SESSION['success'] = "Patient added successfully";
        }

        // Edit Patient
        if (isset($_POST['edit_patient'])) {
            $patient_id = $_POST['patient_id'];
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $address = $_POST['address'];
            $location = $_POST['location'];
            
            $stmt = $conn->prepare("UPDATE patients SET name = ?, phone = ?, address = ?, location = ? WHERE patient_id = ?");
            $stmt->execute([$name, $phone, $address, $location, $patient_id]);
            
            $_SESSION['success'] = "Patient updated successfully";
        }

        // Delete Patient
        if (isset($_POST['delete_patient'])) {
            $patient_id = $_POST['patient_id'];
            $stmt = $conn->prepare("DELETE FROM patients WHERE patient_id = ?");
            $stmt->execute([$patient_id]);
            $_SESSION['success'] = "Patient deleted successfully";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Fetch data for dashboard
try {
    // Get counts
    $stmt = $conn->query("SELECT COUNT(*) FROM patients");
    $totalPatients = $stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM doctors");
    $totalDoctors = $stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()");
    $todayAppointments = $stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'");
    $pendingAppointments = $stmt->fetchColumn();

    // Fetch recent appointments
    $stmt = $conn->query("
        SELECT a.*, p.name as patient_name, d.name as doctor_name 
        FROM appointments a 
        JOIN patients p ON a.patient_id = p.patient_id 
        JOIN doctors d ON a.doctor_id = d.doctor_id 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC 
        LIMIT 10
    ");
    $recentAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch doctors
    $stmt = $conn->query("SELECT * FROM doctors ORDER BY name");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch patients
    $stmt = $conn->query("SELECT * FROM patients ORDER BY name");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching data: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clinic Management System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'include/header.php'; ?>
<div class="admin-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
    </div>
    <div class="admin-tabs">
    <button class="tab-btn active" onclick="showTab('dashboard')">Dashboard</button>
    <button class="tab-btn" onclick="showTab('appointments')">Appointments</button>
    <button class="tab-btn" onclick="showTab('doctors')">Doctors</button>
    <button class="tab-btn" onclick="showTab('patients')">Patients</button>
    <button class="tab-btn" onclick="showTab('admins')">Admins</button>
</div>
    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <h3>Total Patients</h3>
            <p><?php echo $totalPatients; ?></p>
        </div>
        <div class="stat-card">
            <i class="fas fa-user-md"></i>
            <h3>Total Doctors</h3>
            <p><?php echo $totalDoctors; ?></p>
        </div>
        <div class="stat-card">
            <i class="fas fa-calendar-check"></i>
            <h3>Today's Appointments</h3>
            <p><?php echo $todayAppointments; ?></p>
        </div>
        <div class="stat-card">
            <i class="fas fa-clock"></i>
            <h3>Pending Appointments</h3>
            <p><?php echo $pendingAppointments; ?></p>
        </div>
    </div>

    <!-- Main Tabs -->
    <div class="admin-tabs">
        <button class="tab-btn active" onclick="showTab('dashboard')">Dashboard</button>
        <button class="tab-btn" onclick="showTab('appointments')">Appointments</button>
        <button class="tab-btn" onclick="showTab('doctors')">Doctors</button>
        <button class="tab-btn" onclick="showTab('patients')">Patients</button>
    </div>

    <!-- Tab Contents -->
    <div id="dashboard" class="tab-content active">
        <h2>Recent Appointments</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentAppointments as $appointment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                        <td>
                            <span class="status-badge <?php echo strtolower($appointment['status']); ?>">
                                <?php echo htmlspecialchars($appointment['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button onclick="viewAppointment(<?php echo $appointment['appointment_id']; ?>)" 
                                    class="btn-view">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Appointments Tab -->
    <div id="appointments" class="tab-content">
        <h2>Manage Appointments</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentAppointments as $appointment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                        <td>
                            <select onchange="updateAppointmentStatus(<?php echo $appointment['appointment_id']; ?>, this.value)">
                                <option value="Pending" <?php echo $appointment['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Confirmed" <?php echo $appointment['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="Cancelled" <?php echo $appointment['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="Completed" <?php echo $appointment['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </td>
                        <td>
                            <button onclick="viewAppointment(<?php echo $appointment['appointment_id']; ?>)" 
                                    class="btn-view">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Doctors Tab -->
    <div id="doctors" class="tab-content">
        <h2>Manage Doctors</h2>
        <button onclick="showModal('add-doctor-modal')" class="btn-add">
            <i class="fas fa-plus"></i> Add New Doctor
        </button>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Specialization</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $doctor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['contactinfo']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                        <td>
                            <button onclick="editDoctor(<?php echo $doctor['doctor_id']; ?>)" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteDoctor(<?php echo $doctor['doctor_id']; ?>)" class="btn-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Patients Tab -->
    <div id="patients" class="tab-content">
        <h2>Manage Patients</h2>
        <button onclick="showModal('add-patient-modal')" class="btn-add">
            <i class="fas fa-plus"></i> Add New Patient
        </button>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $patient): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($patient['name']); ?></td>
                        <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                        <td><?php echo htmlspecialchars($patient['address']); ?></td>
                        <td><?php echo htmlspecialchars($patient['location']); ?></td>
                        <td>
                            <button onclick="editPatient(<?php echo $patient['patient_id']; ?>)" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deletePatient(<?php echo $patient['patient_id']; ?>)" class="btn-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Admins Tab -->
<dv id="admins" class="tab-content">
    <h2>Manage Administrators</h2>
    <button onclick="showModal('add-admin-modal')" class="btn-add">
        <i class="fas fa-plus"></i> Add New Admin
    </button>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Contact Info</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $stmt = $conn->query("SELECT * FROM users WHERE role = 'admin'");
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($admins as $admin): 
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($admin['name']); ?></td>
                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                    <td><?php echo htmlspecialchars($admin['contactinfo']); ?></td>
                    <td>
                        <button onclick="editAdmin(<?php echo $admin['user_id']; ?>)" class="btn-edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteAdmin(<?php echo $admin['user_id']; ?>)" class="btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Doctor Modal -->
<div id="add-doctor-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('add-doctor-modal')">&times;</span>
        <h2>Add New Doctor</h2>
        <form method="POST">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Contact Info:</label>
                <input type="text" name="contactinfo" required>
            </div>
            <div class="form-group">
                <label>Specialization:</label>
                <input type="text" name="specialization" required>
            </div>
            <button type="submit" name="add_doctor" class="btn-submit">Add Doctor</button>
        </form>
    </div>
</div>

<!-- Edit Doctor Modal -->
<div id="edit-doctor-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('edit-doctor-modal')">&times;</span>
        <h2>Edit Doctor</h2>
        <form method="POST">
            <input type="hidden" id="edit-doctor-id" name="doctor_id">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" id="edit-doctor-name" name="name" required>
            </div>
            <div class="form-group">
                <label>Contact Info:</label>
                <input type="text" id="edit-doctor-contact" name="contactinfo" required>
            </div>
            <div class="form-group">
                <label>Specialization:</label>
                <input type="text" id="edit-doctor-specialization" name="specialization" required>
            </div>
            <button type="submit" name="edit_doctor" class="btn-submit">Update Doctor</button>
        </form>
    </div>
</div>

<!-- Add Patient Modal -->
<div id="add-patient-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('add-patient-modal')">&times;</span>
        <h2>Add New Patient</h2>
        <form method="POST">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Phone:</label>
                <input type="text" name="phone" required>
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address" required>
            </div>
            <div class="form-group">
                <label>Location:</label>
                <input type="text" name="location" required>
            </div>
            <button type="submit" name="add_patient" class="btn-submit">Add Patient</button>
        </form>
    </div>
</div>

<!-- Edit Patient Modal -->
<div id="edit-patient-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('edit-patient-modal')">&times;</span>
        <h2>Edit Patient</h2>
        <form method="POST">
            <input type="hidden" id="edit-patient-id" name="patient_id">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" id="edit-patient-name" name="name" required>
            </div>
            <div class="form-group">
                <label>Phone:</label>
                <input type="text" id="edit-patient-phone" name="phone" required>
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" id="edit-patient-address" name="address" required>
            </div>
            <div class="form-group">
                <label>Location:</label>
                <input type="text" id="edit-patient-location" name="location" required>
            </div>
            <button type="submit" name="edit_patient" class="btn-submit">Update Patient</button>
        </form>
    </div>
</div>

<!-- View Appointment Modal -->
<div id="view-appointment-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('view-appointment-modal')">&times;</span>
        <h2>Appointment Details</h2>
        <div id="appointment-details"></div>
    </div>
</div>

<script>
// Tab switching
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    event.target.classList.add('active');
}

// View Appointment Details
function viewAppointment(appointmentId) {
    fetch(`admindashboard.php?action=get_appointment&id=${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            showModal('view-appointment-modal');
            document.getElementById('appointment-details').innerHTML = `
                <p><strong>Patient:</strong> ${data.patient_name}</p>
                <p><strong>Doctor:</strong> ${data.doctor_name}</p>
                <p><strong>Date:</strong> ${data.appointment_date}</p>
                <p><strong>Time:</strong> ${data.appointment_time}</p>
                <p><strong>Location:</strong> ${data.location}</p>
                <p><strong>Reason:</strong> ${data.reason}</p>
                <p><strong>Status:</strong> ${data.status}</p>
            `;
        })
        .catch(error => console.error('Error:', error));
}

// Update Appointment Status
function updateAppointmentStatus(appointmentId, status) {
    const formData = new FormData();
    formData.append('appointment_id', appointmentId);
    formData.append('status', status);
    formData.append('update_appointment', '1');

    fetch('admindashboard.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        location.reload();
    })
    .catch(error => console.error('Error:', error));
}

// Edit Doctor
function editDoctor(doctorId) {
    fetch(`admindashboard.php?action=get_doctor&id=${doctorId}`)
        .then(response => response.json())
        .then(data => {
            showModal('edit-doctor-modal');
            document.getElementById('edit-doctor-id').value = doctorId;
            document.getElementById('edit-doctor-name').value = data.name;
            document.getElementById('edit-doctor-contact').value = data.contactinfo;
            document.getElementById('edit-doctor-specialization').value = data.specialization;
        })
        .catch(error => console.error('Error:', error));
}

// Delete Doctor
function deleteDoctor(doctorId) {
    if (confirm('Are you sure you want to delete this doctor?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="delete_doctor" value="1">
            <input type="hidden" name="doctor_id" value="${doctorId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Edit Patient
function editPatient(patientId) {
    fetch(`admindashboard.php?action=get_patient&id=${patientId}`)
        .then(response => response.json())
        .then(data => {
            showModal('edit-patient-modal');
            document.getElementById('edit-patient-id').value = patientId;
            document.getElementById('edit-patient-name').value = data.name;
            document.getElementById('edit-patient-phone').value = data.phone;
            document.getElementById('edit-patient-address').value = data.address;
            document.getElementById('edit-patient-location').value = data.location;
        })
        .catch(error => console.error('Error:', error));
}

// Delete Patient
function deletePatient(patientId) {
    if (confirm('Are you sure you want to delete this patient?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="delete_patient" value="1">
            <input type="hidden" name="patient_id" value="${patientId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Modal functions
function showModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
// Edit Admin
function editAdmin(adminId) {
    fetch(`admindashboard.php?action=get_admin&id=${adminId}`)
        .then(response => response.json())
        .then(data => {
            showModal('edit-admin-modal');
            document.getElementById('edit-admin-id').value = adminId;
            document.getElementById('edit-admin-name').value = data.name;
            document.getElementById('edit-admin-username').value = data.username;
            document.getElementById('edit-admin-contact').value = data.contactinfo;
        })
        .catch(error => console.error('Error:', error));
}

// Delete Admin
function deleteAdmin(adminId) {
    if (confirm('Are you sure you want to delete this administrator?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="delete_admin" value="1">
            <input type="hidden" name="admin_id" value="${adminId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<!-- Add Admin Modal -->
<div id="add-admin-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('add-admin-modal')">&times;</span>
        <h2>Add New Administrator</h2>
        <form method="POST">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Contact Info:</label>
                <input type="text" name="contactinfo" required>
            </div>
            <button type="submit" name="add_admin" class="btn-submit">Add Administrator</button>
        </form>
    </div>
</div>

<!-- Edit Admin Modal -->
<div id="edit-admin-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('edit-admin-modal')">&times;</span>
        <h2>Edit Administrator</h2>
        <form method="POST">
            <input type="hidden" id="edit-admin-id" name="admin_id">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" id="edit-admin-name" name="name" required>
            </div>
            <div class="form-group">
                <label>Username:</label>
                <input type="text" id="edit-admin-username" name="username" required>
            </div>
            <div class="form-group">
                <label>Contact Info:</label>
                <input type="text" id="edit-admin-contact" name="contactinfo" required>
            </div>
            <div class="form-group">
                <label>New Password (leave blank to keep current):</label>
                <input type="password" name="password">
            </div>
            <button type="submit" name="edit_admin" class="btn-submit">Update Administrator</button>
        </form>
    </div>
</div>
<?php include 'include/footer.php'; ?>
</body>
</html>