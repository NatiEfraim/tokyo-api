
<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>distribution Notification</title>
    <style>
        /* Define CSS classes for different colors */
        .color-0 {
            background-color: white;
        }

        /* Regular color */
        .color-1 {
            background-color: red;
        }

        /* Red */
        .color-2 {
            background-color: yellow;
        }

        /* Yellow */
        .color-3 {
            background-color: green;
        }

        /* Green */

        /* Center align the content within each cell */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            /* Center align the content */
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>טבלת ניפוק פריטים</h1>
    <table dir="rtl">
        <thead>
            <tr>
                <th>מספר שורה</th>
                <th>מזהה שורה</th>
                <th>תאריך ניפוק</th>
                <th>שם מחלקה</th>
                <th>מספר אישי</th>
                <th>שם מלא</th>
                <th>סוג עובד</th>
                <th>טלפון</th>
                <th>מייל</th>
                <th>כמות</th>
                <th>מק"ט</th>
                <th>סוג פריט</th>
                <th>פירוט מורחב</th>
                <th>הערות</th>
                <th>סטטוס</th>
                <th>תאריך שינוי אחרון</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($distributions as $key => $distribution)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $distribution->id ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->created_at_date ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->department_id ? $distribution->department->name : 'לא קיים' }}</td>
                    <td>{{ $distribution->created_by ? $distribution->createdByUser->personal_number : 'לא קיים' }}</td>
                    <td>{{ $distribution->created_by ? $distribution->createdByUser->name : 'לא קיים' }}</td>
                    <td>{{ $distribution->created_by ? $distribution->createdByUser->translated_employee_type : 'לא קיים' }}</td>
                    <td>{{ $distribution->created_by ? $distribution->createdByUser->phone : 'לא קיים' }}</td>
                    <td>{{ $distribution->created_by ? $distribution->createdByUser->email : 'לא קיים' }}</td>
                    <td>{{ $distribution->quantity ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->inventory_id ? $distribution->inventory->sku : 'לא קיים' }}</td>
                    <td>{{ $distribution->inventory_id ? $distribution->inventory->item_type : 'לא קיים' }}</td>
                    <td>{{ $distribution->inventory_id ? $distribution->inventory->detailed_description : 'לא קיים' }}</td>
                    <td>{{ $distribution->comment ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->getStatusTranslation() ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->updated_at_date ?? 'לא קיים' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>





{{-- <x-mail::message>
# Introduction

The body of your message.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> --}}
