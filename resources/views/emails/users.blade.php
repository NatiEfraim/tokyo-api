<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>user Notification</title>
    <style>
        /* Center align the content within each cell */
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center; /* Center align the content */
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>טבלת משתמשים</h1>
    <table dir="rtl">
        <thead>
            <tr>
                <th>מספר שורה</th>
                <th>מזהה שורה</th>
                <th>שם משתמש</th>
                <th>מספר אישי</th>
                <th>מייל</th>
                <th>מספר טלפון</th>
                <th>סוג עובד</th>
                <th>נוצר בתאריך</th>
                <th>עודכן בתאריך</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $key => $user)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $user->id ?? 'לא קיים' }}</td>
                <td>{{ $user->name ?? 'לא קיים' }}</td>
                <td>{{ $user->personal_number ?? 'לא קיים' }}</td>
                <td>{{ $user->email ?? 'לא קיים' }}</td>
                <td>{{ $user->phone ?? 'לא קיים' }}</td>
                <td>{{ $user->emp_type_id ? $user->translated_employee_type : 'לא קיים'}}</td>
                <td>{{ $user->created_at_date ?? 'לא קיים' }}</td>
                <td>{{ $user->created_at_date ?? 'לא קיים' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>


