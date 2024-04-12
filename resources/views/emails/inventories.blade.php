<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>inventory Notification</title>
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
    <h1>טבלת מלאי</h1>
    <table dir="rtl">
        <thead>
            <tr>
                <th>מספר שורה</th>
                <th>מזהה שורה</th>
                <th>כמות</th>
                <th>מק"ט</th>
                <th>סוג פריט</th>
                <th>פירוט מורחב</th>
                <th>נוצר בתאריך</th>
                <th>עודכן בתאריך</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inventories as $key => $inventory)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $inventory->id ?? 'חסר' }}</td>
                    <td>{{ $inventory->quantity ?? 'חסר' }}</td>
                    <td>{{ $inventory->sku ?? 'חסר' }}</td>
                    <td>{{ $inventory->item_type ?? 'חסר' }}</td>
                    <td>{{ $inventory->detailed_description ?? 'חסר' }}</td>
                    <td>{{ $inventory->created_at ?? 'חסר' }}</td>
                    <td>{{ $inventory->updated_at ?? 'חסר' }}</td>
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
