<!DOCTYPE html>
<html>
<head>
    <title>Simple Test</title>
</head>
<body>
    <h1>SIMPLE TEST - If you can see this, PHP views are working</h1>
    <p>Total institutions: {{ $stats['total_institutions'] ?? 'N/A' }}</p>
    <p>Total admins: {{ $stats['total_admins'] ?? 'N/A' }}</p>
</body>
</html>