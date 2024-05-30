
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
                <th>מספר הזמנה</th>
                <th>שם מחלקה</th>
                <th>מספר אישי</th>
                <th>שם מלא</th>
                <th>סוג עובד</th>
                <th>טלפון</th>
                <th>מייל</th>
                <th>כמות פריט</th>
                <th>כמות סה"כ</th>
                <th>מק"ט</th>
                <th>סוג פריט</th>
                <th>פירוט מורחב</th>
                <th>הערות על הפריט</th>
                <th>הערות ראש מדור</th>
                <th>הערות מנהל</th>
                <th>הערות אפסנאי</th>
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
                    <td>{{ $distribution->order_number ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->department_id ? $distribution->department->name : 'לא קיים' }}</td>
                    <td>{{ $distribution->created_for ? $distribution->createdForUser->personal_number : 'לא קיים' }}</td>
                    <td>{{ $distribution->created_for ? $distribution->createdForUser->name : 'לא קיים' }}</td>
                    <td>{{ $distribution->created_for ? $distribution->createdForUser->translated_employee_type : 'לא קיים' }}</td>
                    <td>{{ $distribution->created_for ? $distribution->createdForUser->phone : 'לא קיים' }}</td>
                    <td>{{ $distribution->created_for ? $distribution->createdForUser->email : 'לא קיים' }}</td>
                    <td>{{ $distribution->quantity_per_item ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->total_quantity ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->inventory_id ? $distribution->inventory->sku : 'לא קיים' }}</td>
                    <td>{{ $distribution->itemType->type ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->inventory_id ? $distribution->inventory->detailed_description : 'לא קיים' }}</td>
                    <td>{{ $distribution->type_comment ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->user_comment ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->admin_comment ?? 'לא קיים' }}</td>
                    <td>{{ $distribution->quartermaster_comment ?? 'לא קיים' }}</td>
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
