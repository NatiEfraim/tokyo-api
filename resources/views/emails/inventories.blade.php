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
                <th>מלאי זמין</th>
                <th>מק"ט</th>
                <th>סוג פריט</th>
                <th>פירוט מורחב</th>
                <th>שמורים</th>
                <th>נוצר בתאריך</th>
                <th>עודכן בתאריך</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inventories as $key => $inventory)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $inventory->id ?? 'לא קיים' }}</td>
                    <td>{{ $inventory->available ?? 'לא קיים' }}</td>
                    <td>{{ $inventory->sku ?? 'לא קיים' }}</td>
                    {{-- <td>{{ $inventory->item_type ?? 'לא קיים' }}</td> --}}
                    <td>{{ $inventory->itemType->type ?? 'לא קיים' }}</td>
                    <td>{{ $inventory->detailed_description ?? 'לא קיים' }}</td>
                    <td>{{ $inventory->reserved ?? 'לא קיים' }}</td>
                    <td>{{ $inventory->created_at_date ?? 'לא קיים' }}</td>
                    <td>{{ $inventory->updated_at_date ?? 'לא קיים' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

