<?php
$conn = new \mysqli("127.0.0.1", "root", "", "meezanservices");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== CHECKING ADMIN-MANAGEMENT PERMISSIONS ===\n";
$result = $conn->query("SELECT id, slug, module_key, action FROM permissions WHERE module_key = 'admin-management'");
if (!$result) {
    die("Query failed: " . $conn->error);
}

if ($result->num_rows === 0) {
    echo "❌ NO admin-management permissions found!\n";
} else {
    echo "✓ Found " . $result->num_rows . " admin-management permissions:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - ID: {$row['id']}, Slug: {$row['slug']}\n";
    }
}

echo "\n=== CHECKING FULL-ACCESS ROLE ===\n";
$role = $conn->query("SELECT id, name, is_full_access FROM roles WHERE slug = 'full-access'")->fetch_assoc();
if ($role) {
    echo "✓ Full-Access Role ID: {$role['id']}\n";
    echo "  Is Full Access: " . ($role['is_full_access'] ? "YES" : "NO") . "\n";

    $result = $conn->query("SELECT COUNT(*) as cnt FROM permission_role WHERE role_id = {$role['id']}");
    $count = $result->fetch_assoc();
    echo "  Permissions assigned: {$count['cnt']}\n";

    // Check if admin-management is assigned
    $adminMgmt = $conn->query("
        SELECT COUNT(*) as cnt FROM permission_role pr
        JOIN permissions p ON pr.permission_id = p.id
        WHERE pr.role_id = {$role['id']} AND p.module_key = 'admin-management'
    ")->fetch_assoc();
    echo "  Admin-Management permissions assigned: {$adminMgmt['cnt']}\n";
} else {
    echo "❌ Full-Access role not found!\n";
}

echo "\n=== CHECKING LOGGED-IN ADMIN ===\n";
$admin = $conn->query("SELECT id, name, email, is_super_admin, role_id FROM admins LIMIT 1")->fetch_assoc();
if ($admin) {
    echo "✓ Admin ID: {$admin['id']}, Name: {$admin['name']}\n";
    echo "  Is Super Admin: " . ($admin['is_super_admin'] ? "YES" : "NO") . "\n";
    echo "  Role ID: {$admin['role_id']}\n";
} else {
    echo "❌ No admins found!\n";
}

$conn->close();
?>
